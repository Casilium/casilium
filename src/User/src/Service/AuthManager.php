<?php
declare(strict_types=1);

namespace User\Service;

use Exception;
use User\Entity\User;
use UserAuthentication\Entity\Identity;
use function array_pop;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function strpos;
use function substr;

class AuthManager
{
    public const ACCESS_GRANTED = 1;
    public const AUTH_REQUIRED  = 2;
    public const ACCESS_DENIED  = 3;

    /** @var array */
    private $config;

    /** @var RbacManager */
    private $rbacManager;

    /**
     * @param array $config
     */
    public function __construct(RbacManager $rbacManager, array $config)
    {
        $this->rbacManager = $rbacManager;
        $this->config      = $config;
    }

    /**
     * Check to see if current user has access to resource
     *
     * @throws Exception
     */
    public function filterAccess(string $route, ?int $identity = null): int
    {
        $actionName = null;

        if (strpos($route, '.') !== false) {
            $routeParts = explode('.', $route);

            // get route action
            $actionName = array_pop($routeParts);

            // rebuild route
            $route = implode('.', $routeParts);
        }

        $mode = $this->config['options']['mode'] ?? 'restrictive';
        if ($mode !== 'permissive' && $mode !== 'restrictive') {
            throw new Exception('Invalid access filter mode (expected either restrictive or permissive');
        }

        if (isset($this->config['routes'])) {
            $items = $this->config['routes'][$route] ?? [];

            foreach ($items as $item) {
                $actionList = $item['actions'] ?? null;
                $allow      = $item['allow'];

                // else if action is specified
                if (
                    $actionList === '*'
                    || $actionList === null
                    || (is_array($actionList) && in_array($actionName, $actionList, true))
                ) {
                    if ($allow === '*') {
                        // anyone is allowed
                        return self::ACCESS_GRANTED;
                    }

                    if (! $identity) {
                        // only authenticate user is allowed to see the page
                        return self::AUTH_REQUIRED;
                    }

                    if ($allow === '@') {
                        // any authenticated user is allowed to see the page
                        return self::ACCESS_GRANTED;
                    }

                    if (strpos($allow, '@') === 0) {
                        // only the specific user is allowed to see the page
                        $targetIdentity = substr($allow, 1);
                        if ($targetIdentity === $identity) {
                            return self::ACCESS_GRANTED;
                        }

                        return self::ACCESS_DENIED;
                    }

                    if (strpos($allow, '+') === 0) {
                        // only a user with this permission is allowed to see the page
                        $permission = substr($allow, 1);

                        if ($this->rbacManager->isGranted($identity, $permission)) {
                            return self::ACCESS_GRANTED;
                        }

                        return self::ACCESS_DENIED;
                    }

                    throw new Exception('Unexpected value for "allow" expected '
                        . 'either "?", "@", "@identity" or "+permission');
                }
            }
        }

        // in restrictive mode, we require authentication for any action not listed under 'access_filter' key,
        // and deny access to authorized users (for security reasons)
        if ($mode === 'restrictive') {
            if (! $identity) {
                return self::AUTH_REQUIRED;
            }

            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }

    /**
     * @param User $user User to fetch identity for
     * @return Identity user's identity
     */
    public function createIdentityFromArray(array $data): Identity
    {
        $identity = new Identity();
        $identity->setId($data['id']);
        $identity->setEmail($data['email']);
        $identity->setName($data['name']);

        return $identity;
    }
}
