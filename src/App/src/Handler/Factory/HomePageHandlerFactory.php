<?php

declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\HomePageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Ticket;

class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $renderer         = $container->get(TemplateRendererInterface::class);
        $ticketRepository = $container->get(EntityManagerInterface::class)
            ->getRepository(Ticket::class);
        return new HomePageHandler($renderer, $ticketRepository);
    }
}
