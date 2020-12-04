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

/**
 * Display home page
 */
class HomePageHandler implements RequestHandlerInterface
{
    /** @var null|TemplateRendererInterface */
    private $renderer;

    /** @var TicketRepositoryInterface */
    private $ticketRepo;

    public function __construct(
        TemplateRendererInterface $renderer,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->renderer   = $renderer;
        $this->ticketRepo = $ticketRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
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

        $endOfMonth     = Carbon::now('UTC')->endOfMonth();
        $startOfMonth   = Carbon::now('UTC')->startOfMonth();

        $agentStats     = $this->ticketRepo->findAllAgentStats($startOfMonth, $endOfMonth);
        $stats['agent'] = $agentStats;

        return new HtmlResponse($this->renderer->render('app::home-page', [
            'stats' => $stats,
        ]));
    }
}
