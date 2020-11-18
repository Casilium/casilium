<?php

declare(strict_types=1);

namespace UserAuthentication\Handler\Factory;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use UserAuthentication\Handler\LogoutPageHandler;

class LogoutPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): LogoutPageHandler
    {
        $helper = $container->get(UrlHelper::class);
        return new LogoutPageHandler($helper);
    }
}
