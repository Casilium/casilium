<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ticket\Repository\TicketRepository;

class HomePageHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testReturnsHtmlResponse(): void
    {
        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer
            ->render('app::home-page', Argument::type('array'))
            ->willReturn('');

        $ticketRepository = $this->prophesize(TicketRepository::class);
        $ticketRepository->findUnResolvedTicketCount()->willReturn(5);
        $ticketRepository->findOverDueTicketCount()->willReturn(5);
        $ticketRepository->findDueTodayTicketCount()->willReturn(5);
        $ticketRepository->findOpenTicketCount()->willReturn(5);
        $ticketRepository->findOnHoldTicketCount()->willReturn(5);
        $ticketRepository->findTotalTicketCount()->willReturn(5);
        $ticketRepository->findResolvedTicketCount()->willReturn(5);
        $ticketRepository->findClosedTicketCount()->willReturn(5);
        $ticketRepository->findAllAgentStats(Argument::any(), Argument::any())->willReturn([]);

        $homePage = new HomePageHandler($renderer->reveal(), $ticketRepository->reveal());
        $response = $homePage->handle($this->prophesize(ServerRequestInterface::class)->reveal());

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
