<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;

class ViewTicketHandler implements RequestHandlerInterface
{
    /** @var TicketService */
    protected $ticketService;

    /** @var TicketHydrator */
    protected $hydrator;

    /** @var TemplateRendererInterface */
    protected $renderer;

    public function __construct(
        TicketService $ticketService,
        TicketHydrator $ticketHydrator,
        TemplateRendererInterface $renderer
    ) {
        $this->ticketService = $ticketService;
        $this->hydrator      = $ticketHydrator;
        $this->renderer      = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $ticketUuid = $request->getAttribute('ticket_id');
        $ticket = $this->ticketService->getTicketByUuid($ticketUuid);

        return new HtmlResponse($this->renderer->render('ticket::view-ticket', ['ticket' => $ticket]));
    }

}