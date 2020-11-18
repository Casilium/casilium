<?php

declare(strict_types=1);

namespace Account\Handler\Factory;

use Account\Handler\AccountPageHandler;
use Interop\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

class AccountPageHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $renderer = $container->get(TemplateRendererInterface::class);
        return new AccountPageHandler($renderer);
    }
}
