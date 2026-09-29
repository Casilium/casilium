<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Factory\HomePageHandlerFactory;
use App\Handler\HomePageHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;

class HomePageHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInstantiateFactory()
    {
        $templateRenderInterface = $this->prophesize(TemplateRendererInterface::class);
        $ticketRepository        = $this->prophesize(TicketRepository::class);

        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager->getRepository(Ticket::class)->willReturn($ticketRepository->reveal());

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->wilLReturn($entityManager->reveal());
        $container->get(TemplateRendererInterface::class)->willReturn($templateRenderInterface->reveal());
        $container->get('config')->willReturn(['tickets' => ['auto_close_days' => 2]]);

        $factory  = new HomePageHandlerFactory();
        $homePage = $factory($container->reveal());
        $this->assertInstanceOf(HomePageHandler::class, $homePage);
    }

    public function testFallsBackWhenTheAutoCloseSettingIsMissing(): void
    {
        $templateRenderInterface = $this->prophesize(TemplateRendererInterface::class);
        $ticketRepository        = $this->prophesize(TicketRepository::class);

        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager->getRepository(Ticket::class)->willReturn($ticketRepository->reveal());

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(EntityManagerInterface::class)->willReturn($entityManager->reveal());
        $container->get(TemplateRendererInterface::class)->willReturn($templateRenderInterface->reveal());
        $container->get('config')->willReturn([]);

        $factory = new HomePageHandlerFactory();

        $this->assertInstanceOf(HomePageHandler::class, $factory($container->reveal()));
    }
}
