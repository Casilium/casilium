<?php

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Service\TicketService;

class ListTicketHandler implements RequestHandlerInterface
{
    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * ListTicketHandler constructor.
     *
     * @param TicketService $ticketService
     */
    public function __construct(TicketService $ticketService, TemplateRendererInterface $renderer)
    {
        $this->ticketService = $ticketService;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tickets = $this->ticketService->fetchAllTickets();

        return new HtmlResponse($this->renderer->render('ticket::ticket-list', ['tickets' => $tickets]));
    }
}