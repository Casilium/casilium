<?php

declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\HomePageHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class HomePageHandlerFactory
 *
 * @package App\Handler\Factory
 */
class HomePageHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return RequestHandlerInterface
     */
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $renderer = $container->get(TemplateRendererInterface::class);
        return new HomePageHandler($renderer);
    }
}
