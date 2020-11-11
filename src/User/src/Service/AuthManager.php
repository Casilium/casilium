<?php
declare(strict_types=1);

namespace User\Service;

class AuthManager
{
    public const ACCESS_GRANTED = 1;
    public const AUTH_REQUIRED  = 2;
    public const ACCESS_DENIED  = 3;

    /**
     * @var array
     */
    private $config;

    /**
     * @var RbacManager
     */
    private $rbacManager;

    public function __construct(RbacManager $rbacManager, array $config)
    {
        $this->rbacManager = $rbacManager;
        $this->config = $config;
    }

    /**
     * Check to see if current user has access to resource
     * @param string $route
     * @param string|null $identity
     * @return int
     * @throws \Exception
     */
    public function filterAccess(string $route, string $identity = null): int
    {
        $actionName = null;

        if (strpos($route, '.') !== false) {
            $routeParts = explode('.', $route);

            // get route action
            $actionName = array_pop($routeParts);
            //$actionName = end($routeParts);

            // rebuild route
            $route = implode('.', $routeParts);
        }

        $mode = $this->config['options']['mode'] ?? 'restrictive';
        if ($mode != 'permissive' && $mode != 'restrictive') {
            throw new \Exception('Invalid access filter mode (expected either restrictive or permissive');
        }

        if (isset($this->config['routes'])) {
            $items = $this->config['routes'][$route] ?? [];


            foreach ($items as $item) {
                $actionList = $item['actions'] ?? null;
                $allow = $item['allow'];

                // else if action is specified
                if ((is_array($actionList) && in_array($actionName, $actionList, true) || $actionList == '*')
                    || $actionList == null) {
                    if ($allow == '*') {
                        // anyone is allowed
                        return self::ACCESS_GRANTED;
                    } elseif (! $identity) {
                        // only authenticate user is allowed to see the page
                        return self::AUTH_REQUIRED;
                    }

                    if ($allow == '@') {
                        // any authenticated user is allowed to see the page
                        return self::ACCESS_GRANTED;
                    } elseif (substr($allow, 0, 1) == '@') {
                        // only the specific user is allowed to see the page
                        $target_identity = substr($allow, 1);
                        if ($target_identity == $identity) {
                            return self::ACCESS_GRANTED;
                        } else {
                            return self::ACCESS_DENIED;
                        }
                    } elseif (substr($allow, 0, 1) == '+') {
                        // only a user with this permission is allowed to see the page
                        $permission = substr($allow, 1);

                        if ($this->rbacManager->isGranted($identity, $permission)) {
                            return self::ACCESS_GRANTED;
                        } else {
                            return self::ACCESS_DENIED;
                        }
                    } else {
                        throw new \Exception('Unexpected value for "allow" expected ' .
                            'either "?", "@", "@identity" or "+permission');
                    }
                }
            }
        }

        // in restrictive mode, we require authentication for any action not listed under 'access_filter' key,
        // and deny access to authorized users (for security reasons)
        if ($mode == 'restrictive') {
            if (! $identity) {
                return self::AUTH_REQUIRED;
            } else {
                return self::ACCESS_DENIED;
            }
        }

        return self::ACCESS_GRANTED;
    }
}
