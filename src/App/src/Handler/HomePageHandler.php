<?php

declare(strict_types=1);

namespace App\Handler;

use Carbon\Carbon;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Repository\TicketRepositoryInterface;

use function number_format;
use function sprintf;

/**
 * Display home page
 */
class HomePageHandler implements RequestHandlerInterface
{
    /** Rolling window the dashboard reports over, in days */
    private const REPORTING_DAYS = 30;

    private TemplateRendererInterface $renderer;
    private TicketRepositoryInterface $ticketRepo;
    private int $autoCloseDays;

    public function __construct(
        TemplateRendererInterface $renderer,
        TicketRepositoryInterface $ticketRepository,
        int $autoCloseDays = 2
    ) {
        $this->renderer      = $renderer;
        $this->ticketRepo    = $ticketRepository;
        $this->autoCloseDays = $autoCloseDays;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $periodEnd   = Carbon::now('UTC');
        $periodStart = $periodEnd->copy()->subDays(self::REPORTING_DAYS);

        $stats = [
            'periodLabel' => sprintf('Last %d days', self::REPORTING_DAYS),
            'unresolved'  => $this->ticketRepo->findUnresolvedTicketCount(),
            'overdue'     => $this->ticketRepo->findOverdueTicketCount(),
            'dueToday'    => $this->ticketRepo->findDueTodayTicketCount(),
            'open'        => $this->ticketRepo->findOpenTicketCount(),
            'hold'        => $this->ticketRepo->findOnHoldTicketCount(),
            'created'     => $this->ticketRepo->findTotalTicketCount(),
            'resolved'    => $this->ticketRepo->findResolvedTicketCount(),
            'closed'      => $this->ticketRepo->findClosedTicketCount(),
            'autoClose'   => sprintf(
                'auto-closes after %d day%s',
                $this->autoCloseDays,
                $this->autoCloseDays === 1 ? '' : 's'
            ),
        ];

        $slaResolution     = $this->ticketRepo->findResolutionStats($periodStart, $periodEnd, true);
        $serviceResolution = $this->ticketRepo->findResolutionStats($periodStart, $periodEnd, false);

        $stats['sla'] = [
            'compliance' => $this->ticketRepo->findSlaComplianceRate($periodStart, $periodEnd),
            'resolved'   => $slaResolution['count'],
            'median'     => $this->formatResolutionDuration($slaResolution['median']),
            'mean'       => $this->formatResolutionDuration($slaResolution['mean']),
        ];

        $stats['service'] = [
            'resolved' => $serviceResolution['count'],
            'median'   => $this->formatResolutionDuration($serviceResolution['median']),
            'mean'     => $this->formatResolutionDuration($serviceResolution['mean']),
        ];

        $stats['agent'] = $this->ticketRepo->findAllAgentStats($periodStart, $periodEnd);

        return new HtmlResponse($this->renderer->render('app::home-page', [
            'stats' => $stats,
        ]));
    }

    private function formatResolutionDuration(float $hours): string
    {
        if ($hours >= 1) {
            return number_format($hours, 1) . 'h';
        }

        return number_format($hours * 60, 0) . 'm';
    }
}
