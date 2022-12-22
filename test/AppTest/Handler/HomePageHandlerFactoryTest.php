<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\Factory\HomePageHandlerFactory;
use App\Handler\HomePageHandler;
use Doctrine\ORM\EntityManager;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Ticket\Repository\TicketRepository;

class HomePageHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        // Setup container
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $ticketRepository = $this->prophesize(TicketRepository::class);
        $entityManager    = $this->prophesize(EntityManager::class);
        $entityManager
            ->getRepository(Argument::any())
            ->willReturn($ticketRepository->reveal());

        $this->container->get(EntityManager::class)
            ->willReturn($entityManager->reveal());
    }

    public function testInstantiateFactory()
    {
        $factory = new HomePageHandlerFactory();

        $homePage = $factory($this->container->reveal());

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
