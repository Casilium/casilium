<?php
declare(strict_types=1);

namespace User\Middleware\Factory;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use User\Middleware\AuthorisationMiddleware;
use User\Service\AuthManager;
use User\Service\RbacManager;

class AuthorisationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthorisationMiddleware
    {
        $authManager = $container->get(AuthManager::class);
        $rbacManager = $container->get(RbacManager::class);
        $router      = $container->get(RouterInterface::class);
        $urlHelper   = $container->get(UrlHelper::class);
        $renderer    = $container->get(TemplateRendererInterface::class);

        return new AuthorisationMiddleware($router, $urlHelper, $rbacManager, $authManager, $renderer);
    }
}
