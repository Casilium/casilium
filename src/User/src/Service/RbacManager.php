<?php

declare(strict_types=1);

namespace User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\Rbac;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use User\Entity\Role;
use User\Entity\User;

use function serialize;
use function sprintf;
use function unserialize;

class RbacManager
{
    private array $assertionManagers = [];

    private AdapterInterface $cache;

    private EntityManagerInterface $entityManager;

    private ?Rbac $rbac;

    public function __construct(AdapterInterface $cache, EntityManagerInterface $entityManager)
    {
        $this->cache         = $cache;
        $this->entityManager = $entityManager;
        $this->rbac          = null;
    }

    /**
     * Initialize the RBAC container
     *
     * @param bool $forceCreate Force creation if already initialized
     */
    public function init(bool $forceCreate = false): bool
    {
        if ($this->rbac !== null && ! $forceCreate) {
            return false;
        }

        // If user wants us to init RBAC container, clear cache now
        if ($forceCreate) {
            if ($this->cache->hasItem('rbac_container')) {
                $this->cache->deleteItem('rbac_container');
            }
        }

        $rbacCache = $this->cache->getItem('rbac_container');
        if ($rbacCache->isHit()) {
            $this->rbac = unserialize($rbacCache->get());
        }

        if (! $this->rbac instanceof Rbac) {
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
            $rbacCache->set(serialize($this->rbac));
        }

        return true;
    }

    /**
     * Check if user access to resource
     *
     * @throws Exception
     */
    public function isGranted(int $identity, string $permission, ?string $params = null): bool
    {
        if ($this->rbac === null) {
            $this->init();
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($identity);

        if ($user === null) {
            throw new Exception(sprintf('No such user "%s"', $identity));
        }

        $roles = $user->getRoles();

        /** @var Role $role */
        foreach ($roles as $role) {
            if ($params === null && $this->rbac->isGranted($role->getName(), $permission)) {
                return true;
            }

            foreach ($this->assertionManagers as $assertionManager) {
                if ($assertionManager->assert($this->rbac, $permission, $params)) {
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

    public function addAssertion(AssertionInterface $assertion): RbacManager
    {
        $this->assertionManagers[] = $assertion;
        return $this;
    }
}
