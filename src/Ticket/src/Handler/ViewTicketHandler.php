<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Ticket\Form\TicketResponseForm;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\CannedResponseManager;
use Ticket\Service\TicketService;
use UserAuthentication\Entity\IdentityInterface;

use function is_array;

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

    private CannedResponseManager $cannedResponseManager;

    public function __construct(
        TicketService $ticketService,
        TicketHydrator $ticketHydrator,
        TemplateRendererInterface $renderer,
        UrlHelper $urlHelper,
        CannedResponseManager $cannedResponseManager
    ) {
        $this->ticketService         = $ticketService;
        $this->hydrator              = $ticketHydrator;
        $this->renderer              = $renderer;
        $this->urlHelper             = $urlHelper;
        $this->cannedResponseManager = $cannedResponseManager;
    }

    #[Override]
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
        $contact = $ticket->getContact();
        if (null === $contact) {
            throw new RuntimeException('Ticket has no contact');
        }
        $recentTickets   = $this->ticketService->findRecentTicketsByContact($contact->getId());
        $responseForm    = new TicketResponseForm();
        $cannedResponses = $this->cannedResponseManager->findAll();

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $responseForm->setData(is_array($parsedBody) ? $parsedBody : []);

            if ($responseForm->isValid()) {
                // get filtered form data
                $data = $responseForm->getData(FormInterface::VALUES_AS_ARRAY);

                // pass agent id to save
                $data['agent_id'] = $agentId;
                $this->ticketService->saveResponse($ticket, $data);

                return new RedirectResponse($this->urlHelper->generate('ticket.list'));
            }
        }

        return new HtmlResponse($this->renderer->render('ticket::view-ticket', [
            'ticket'          => $ticket,
            'recentTickets'   => $recentTickets,
            'responseForm'    => $responseForm,
            'responses'       => $responses,
            'cannedResponses' => $cannedResponses,
        ]));
    }
}
