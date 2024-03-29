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
use Ticket\Form\TicketResponseForm;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;
use UserAuthentication\Entity\IdentityInterface;

class ViewTicketHandler implements RequestHandlerInterface
{
    /** @var TicketService */
    protected $ticketService;

    /** @var TicketHydrator */
    protected $hydrator;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        TicketService $ticketService,
        TicketHydrator $ticketHydrator,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper
    ) {
        $this->ticketService = $ticketService;
        $this->hydrator      = $ticketHydrator;
        $this->renderer      = $renderer;
        $this->urlHelper     = $urlHelper;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user    = $request->getAttribute(IdentityInterface::class);
        $agentId = $user->getId();

        // get ticket uuid from URL and find ticket
        $ticketUuid = $request->getAttribute('ticket_id');
        $ticket     = $this->ticketService->getTicketByUuid($ticketUuid);

        // get ticket responses
        $responses = $this->ticketService->findTicketResponses($ticket->getId());

        // find recent tickets
        $recentTickets = $this->ticketService->findRecentTicketsByContact($ticket->getContact()->getId());

        $responseForm = new TicketResponseForm();

        if ($request->getMethod() === 'POST') {
            $responseForm->setData($request->getParsedBody());

            if ($responseForm->isValid()) {
                // get filtered form data
                $data = $responseForm->getData();

                // pass agent id to save
                $data['agent_id'] = $agentId;

                $response = $this->ticketService->saveResponse($ticket, $data);

                return new RedirectResponse($this->urlHelper->generate('ticket.list'));
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::view-ticket', [
            'ticket'        => $ticket,
            'recentTickets' => $recentTickets,
            'responseForm'  => $responseForm,
            'responses'     => $responses,
        ]));
    }
}
