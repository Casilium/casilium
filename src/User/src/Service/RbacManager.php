<?php
declare(strict_types=1);

namespace User\Service;

use User\Entity\Role;
use User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Permissions\Rbac\Rbac;

class RbacManager
{
    /**
     * @var array
     */
    private $assertionManagers = [];

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * RbacManager constructor.
     *
     * @param StorageInterface $cache
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(StorageInterface $cache, EntityManagerInterface $entityManager)
    {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
    }

    /**
     * Initialize the RBAC container
     *
     * @param bool $forceCreate Force creation if already initialized
     * @return bool
     */
    public function init(bool $forceCreate = false): bool
    {
        if ($this->rbac !== null && ! $forceCreate) {
            return false;
        }

        // If user wants us to reinit RBAC container, clear cache now
        if ($forceCreate) {
            $this->cache->removeItem('rbac_container');
        }

        // try to load Rbac container from cache
        $result = false;
        $this->rbac = $this->cache->getItem('rbac_container', $result);
        if (! $result) {
            $this->rbac = new Rbac();
            $this->rbac->setCreateMissingRoles(true);

            $roles = $this->entityManager->getRepository(Role::class)->findBy([], ['id' => 'ASC']);

            /** @var Role $role */
            foreach ($roles as $role) {
                $roleName = $role->getName();

                $parentRoleNames = [];
                foreach ($role->getParentRoles() as $parentRole) {
                    $parentRoleNames[] = $parentRole->getName();
                }

                $this->rbac->addRole($roleName, $parentRoleNames);
                foreach ($role->getPermissions() as $permission) {
                    $this->rbac->getRole($roleName)->addPermission($permission->getName());
                }
            }

            // save Rbac container to the cache
            $this->cache->setItem('rbac_container', $this->rbac);
        }
        return true;
    }

    /**
     * Check if user access access to resource
     *
     * @param string $identity
     * @param string $permission
     * @param string|null $params
     * @return bool
     * @throws \Exception
     */
    public function isGranted(string $identity, string $permission, string $params = null): bool
    {
        if ($this->rbac === null) {
            $this->init();
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($identity);

        if ($user === null) {
            throw new \Exception(sprintf('No such user "%s"', $identity));
        }

        $roles = $user->getRoles();

        /** @var Role $role */
        foreach ($roles as $role) {
            if ($params === null && $this->rbac->isGranted($role->getName(), $permission)) {
                return true;
            }

            foreach ($this->assertionManagers as $assertionManager) {
                if ($assertionManager->asset($this->rbac, $permission, $params)) {
                    return true;
                }
            }

            $childRoles = $role->getChildRoles();
            foreach ($childRoles as $childRole) {
                if ($this->rbac->isGranted($childRole->getName(), $permission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
