<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;
use function intval;

class GoToTicketHandler implements RequestHandlerInterface
{
    /** @var TicketService */
    protected $ticketService;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        TicketService $ticketService,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->ticketService = $ticketService;
        $this->renderer      = $renderer;
        $this->urlHelper     = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // get ticket id from Url
        $ticketId = intval($request->getAttribute('ticket_id'));
        if (! $ticketId) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        // find ticket
        $ticket = $this->ticketService->findTicketById($ticketId);
        if (! $ticket instanceof Ticket) {
            return new HtmlResponse($this->renderer->render('error::404'), 404);
        }

        return new RedirectResponse($this->urlHelper->generate('ticket.view', [
            'ticket_id' => $ticket->getUuid(),
        ]));
    }
}
