<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Factory\HomePageHandlerFactory;
use App\Handler\HomePageHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
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

        $factory  = new HomePageHandlerFactory();
        $homePage = $factory($container->reveal());
        $this->assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
