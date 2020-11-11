<?php

declare(strict_types=1);

namespace UserAuthentication;

use Mezzio\Application;
use UserAuthentication\Handler\Factory\LogoutPageHandlerFactory;
use UserAuthentication\Handler\LogoutPageHandler;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'templates'     => $this->getTemplates(),
            'view_helpers'  => $this->getViewHelperConfig(),
        ];
    }

    /**
     * Returns container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
                Application::class => [
                    RouterDelegator::class,
                ],
            ],
            'aliases' => [
                \Mezzio\Authentication\UserRepositoryInterface::class =>
                    \Mezzio\Authentication\UserRepository\PdoDatabase::class,
            ],
            'factories' => [
                Handler\LoginPageHandler::class => Handler\Factory\LoginPageHandlerFactory::class,
                Handler\LogoutPageHandler::class => Handler\Factory\LogoutPageHandlerFactory::class,

                \Mezzio\Authentication\AuthenticationInterface::class =>
                    \Mezzio\Authentication\Session\PhpSessionFactory::class,

            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates() : array
    {
        return [
            'paths' => [
                'user_auth' => [
                    __DIR__ . '/../templates/'
                ],
            ],
        ];
    }
    /**
     * Returns the view helper configuration
     */
    public function getViewHelperConfig(): array
    {
        return [
            'aliases' => [
                'identity' => View\Helper\IdentityViewHelper::class,
            ],
            'factories' => [
                View\Helper\IdentityViewHelper::class => View\Helper\Factory\IdentityViewHelperFactory::class,
            ],
        ];
    }
}