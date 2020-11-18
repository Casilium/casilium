<?php
declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use User\Entity\Permission;
use function date;

class PermissionManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RbacManager */
    private $rbacManager;

    public function __construct(EntityManagerInterface $entityManager, RbacManager $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->rbacManager   = $rbacManager;
    }

    /**
     * Add permission
     *
     * @param array $data
     * @throws Exception
     */
    public function addPermission(array $data): void
    {
        $existingPermission = $this->entityManager->getRepository(Permission::class)
            ->findOneByName($data['name']);

        if ($existingPermission != null) {
            throw new Exception('Permission with such name already exists');
        }

        $permission = new Permission();
        $permission->setName($data['name']);
        $permission->setDescription($data['description']);
        $permission->setDateCreated(date('Y-m-d H:i:s'));

        $this->entityManager->persist($permission);
        $this->entityManager->flush();

        // reload RBAC container
        $this->rbacManager->init(true);
    }

    /**
     * Update permission
     *
     * @param array $data
     * @throws Exception
     */
    public function updatePermission(Permission $permission, array $data): void
    {
        $existingPermission = $this->entityManager->getRepository(Permission::class)
            ->findOneByName($data['name']);

        if ($existingPermission !== null && $existingPermission !== $permission) {
            throw new Exception('Another permission with such name already exists');
        }

        $permission->setName($data['name']);
        $permission->setDescription($data['description']);

        $this->entityManager->flush();

        // reload RBAC container
        $this->rbacManager->init(true);
    }

    /**
     * Remove permission
     */
    public function deletePermission(Permission $permission): void
    {
        $this->entityManager->remove($permission);
        $this->entityManager->flush();

        // reload RBAC container
        $this->rbacManager->init(true);
    }
}
