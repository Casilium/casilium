<?php

declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\HomePageHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $renderer = $container->get(TemplateRendererInterface::class);
        return new HomePageHandler($renderer);
    }
}
