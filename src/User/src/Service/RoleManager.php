<?php
declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use User\Entity\Permission;
use User\Entity\Role;

class RoleManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RbacManager
     */
    private $rbacManager;

    /**
     * RoleManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RbacManager $rbacManager
     */
    public function __construct(EntityManagerInterface $entityManager, RbacManager $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->rbacManager = $rbacManager;
    }

    /**
     * Add new role to database
     *
     * @param array $data
     * @throws \Exception
     */
    public function addRole(array $data): void
    {
        $existingRole = $this->entityManager->getRepository(Role::class)
            ->findOneByName($data['name']);
        if ($existingRole != null) {
            throw new \Exception('Role with such name already exists');
        }

        $role = new Role();
        $role->setName($data['name']);
        $role->setDescription($data['description']);
        $role->setDateCreated(date('Y-m-d H:i:s'));

        // add parent roles to inherit
        $inheritedRoles = $data['inherit_roles'] ?? [];
        if (count($inheritedRoles) > 0) {
            foreach ($inheritedRoles as $roleId) {
                $parentRole = $this->entityManager->getRepository(Role::class)
                    ->findOneById($roleId);

                if ($parentRole === null) {
                    throw new \Exception('Role to inherit not found');
                }

                if (! $role->getParentRoles()->contains($parentRole)) {
                    $role->addParent($parentRole);
                }
            }
        }

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        // Reload RBAC container
        $this->rbacManager->init(true);
    }

    /**
     * Update an existing role
     *
     * @param Role $role
     * @param array $data
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateRole(Role $role, array $data): void
    {
        $existingRole = $this->entityManager->getRepository(Role::class)
            ->findOneByName($data['name']);

        if ($existingRole !== null && $existingRole !== $role) {
            throw new \Exception('Another role with such name already exists');
        }

        $role->setName($data['name']);
        $role->setDescription($data['description']);

        // clear parent roles so we don't populate the database twice
        $role->clearParentRoles();

        // add the new parent roles to inherit
        $inheritedRoles = $data['inherit_roles'];
        if (count($inheritedRoles) > 0) {
            foreach ($inheritedRoles as $roleId) {
                $parentRole = $this->entityManager->getRepository(Role::class)
                    ->findOneById($roleId);

                if ($parentRole == null) {
                    throw new \Exception('Role to inherit not found');
                }

                if (! $role->getParentRoles()->contains($parentRole)) {
                    $role->addParent($parentRole);
                }
            }
        }

        $this->entityManager->flush();

        // reload RBAC container
        $this->rbacManager->init(true);
    }

    /**
     * Delete a role from DB
     *
     * @param Role $role
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteRole(Role $role): void
    {
        $this->entityManager->remove($role);
        $this->entityManager->flush();
        $this->rbacManager->init(true);
    }

    /**
     * Retrieves all the permissions from the given role and its child roles
     *
     * @param Role $role
     * @return array
     */
    public function getEffectivePermissions(Role $role): array
    {
        $effectivePermissions = [];

        foreach ($role->getParentRoles() as $parentRole) {
            $parentPermissions = $this->getEffectivePermissions($parentRole);
            foreach ($parentPermissions as $name => $inherited) {
                $effectivePermissions[$name] = 'inherited';
            }
        }

        foreach ($role->getPermissions() as $permission) {
            if (! isset($effectivePermissions[$permission->getName()])) {
                $effectivePermissions[$permission->getName()] = 'own';
            }
        }

        return $effectivePermissions;
    }

    /**
     * @param Role $role
     * @param array $data
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateRolePermissions(Role $role, array $data): void
    {
        // remove older permissions
        $role->getPermissions()->clear();

        // assign new permissions to role
        foreach ($data['permissions'] as $name => $isChecked) {
            if (! $isChecked) {
                continue;
            }

            $permission = $this->entityManager->getRepository(Permission::class)->findOneByName($name);
            if ($permission == null) {
                throw new \Exception('Permission with such name does not exist');
            }

            $role->getPermissions()->add($permission);
        }

        // apply changes to database
        $this->entityManager->flush();

        // reload RBAC container
        $this->rbacManager->init(true);
    }
}
