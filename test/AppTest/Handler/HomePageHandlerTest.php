<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
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
        $ticketRepository->findSlaComplianceRate(Argument::any(), Argument::any())->willReturn(99.0);
        $ticketRepository->findResolutionStats(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(['mean' => 2.5, 'median' => 1.5, 'count' => 10]);
        $ticketRepository->findFirstResponseStats(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(['mean' => 0.5, 'median' => 0.25, 'count' => 4]);

        $homePage = new HomePageHandler($renderer->reveal(), $ticketRepository->reveal());
        $response = $homePage->handle($this->prophesize(ServerRequestInterface::class)->reveal());

        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    /**
     * The card reports the median, because one stale ticket cleared out drags
     * the mean into the hundreds while the median stays put.
     */
    public function testReportsTheMedianWithTheMeanBesideIt(): void
    {
        $viewModel = null;

        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer->render('app::home-page', Argument::type('array'))
            ->will(static function (array $args) use (&$viewModel): string {
                $viewModel = $args[1];

                return '';
            });

        $ticketRepository = $this->stubbedRepository();
        $ticketRepository->findResolutionStats(Argument::any(), Argument::any(), true)
            ->willReturn(['mean' => 170.6, 'median' => 4.0, 'count' => 17]);
        $ticketRepository->findResolutionStats(Argument::any(), Argument::any(), false)
            ->willReturn(['mean' => 0.5, 'median' => 0.25, 'count' => 3]);

        $homePage = new HomePageHandler($renderer->reveal(), $ticketRepository->reveal());
        $homePage->handle($this->prophesize(ServerRequestInterface::class)->reveal());

        self::assertSame('4.0h', $viewModel['stats']['sla']['median']);
        self::assertSame('170.6h', $viewModel['stats']['sla']['mean']);
        self::assertSame(17, $viewModel['stats']['sla']['resolved']);
        self::assertSame('15m', $viewModel['stats']['sla']['responseMedian']);

        // under an hour is shown in minutes
        self::assertSame('15m', $viewModel['stats']['service']['median']);
    }

    public function testReportsOverARollingThirtyDayWindow(): void
    {
        $windowInDays = null;

        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer->render('app::home-page', Argument::type('array'))->willReturn('');

        $ticketRepository = $this->stubbedRepository();
        $ticketRepository->findResolutionStats(Argument::any(), Argument::any(), Argument::any())
            ->will(static function (array $args) use (&$windowInDays): array {
                $windowInDays = (int) $args[0]->diffInDays($args[1]);

                return ['mean' => 0.0, 'median' => 0.0, 'count' => 0];
            });

        $homePage = new HomePageHandler($renderer->reveal(), $ticketRepository->reveal());
        $homePage->handle($this->prophesize(ServerRequestInterface::class)->reveal());

        self::assertSame(30, $windowInDays);
    }

    private function stubbedRepository(): ObjectProphecy
    {
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
        $ticketRepository->findSlaComplianceRate(Argument::any(), Argument::any())->willReturn(99.0);
        $ticketRepository->findFirstResponseStats(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(['mean' => 0.5, 'median' => 0.25, 'count' => 4]);

        return $ticketRepository;
    }
}
