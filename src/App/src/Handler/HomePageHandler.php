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

/**
 * Display home page
 */
class HomePageHandler implements RequestHandlerInterface
{
    private TemplateRendererInterface $renderer;
    private TicketRepositoryInterface $ticketRepo;

    public function __construct(
        TemplateRendererInterface $renderer,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->renderer   = $renderer;
        $this->ticketRepo = $ticketRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $endOfMonth   = Carbon::now('UTC')->endOfMonth();
        $startOfMonth = Carbon::now('UTC')->startOfMonth();

        $stats = [
            'unresolved' => $this->ticketRepo->findUnresolvedTicketCount(),
            'overdue'    => $this->ticketRepo->findOverdueTicketCount(),
            'dueToday'   => $this->ticketRepo->findDueTodayTicketCount(),
            'open'       => $this->ticketRepo->findOpenTicketCount(),
            'hold'       => $this->ticketRepo->findOnHoldTicketCount(),
            'created'    => $this->ticketRepo->findTotalTicketCount(),
            'resolved'   => $this->ticketRepo->findResolvedTicketCount(),
            'closed'     => $this->ticketRepo->findClosedTicketCount(),
        ];

        // New metrics
        $stats['sla']                         = [
            'compliance'    => $this->ticketRepo->findSlaComplianceRate($startOfMonth, $endOfMonth),
            'avgResolution' => $this->ticketRepo->findAverageResolutionTime($startOfMonth, $endOfMonth),
            'resolved'      => $this->ticketRepo->findResolvedTicketCountBySlaStatus(true, $startOfMonth, $endOfMonth),
        ];
        $stats['sla']['avgResolutionDisplay'] = $this->formatResolutionDuration($stats['sla']['avgResolution']);

        $stats['service']                         = [
            'avgResolution' => $this->ticketRepo->findAverageResolutionTimeWithoutSla($startOfMonth, $endOfMonth),
            'resolved'      => $this->ticketRepo->findResolvedTicketCountBySlaStatus(false, $startOfMonth, $endOfMonth),
        ];
        $stats['service']['avgResolutionDisplay'] = $this->formatResolutionDuration($stats['service']['avgResolution']);

        $agentStats     = $this->ticketRepo->findAllAgentStats($startOfMonth, $endOfMonth);
        $stats['agent'] = $agentStats;

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
