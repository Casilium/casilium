<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Form\FormInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationSite\Entity\SiteEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Queue;
use Ticket\Form\TicketForm;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;
use UserAuthentication\Entity\IdentityInterface;

use function count;
use function sprintf;

class CreateTicketHandler implements RequestHandlerInterface
{
    protected TicketService $ticketService;

    protected TicketHydrator $hydrator;

    protected TemplateRendererInterface $renderer;

    protected UrlHelper $urlHelper;

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

        $form = new TicketForm();

        if ($ticketId = $request->getAttribute('ticket_id')) {
            $ticket = $this->ticketService->getTicketByUuid($ticketId);
            $ticket->setId(0);
            $form->get('contact_id')->setValue($ticket->getContact()->getId());
            $form->get('type_id')->setValue($ticket->getType()->getId());

            $organisation = $ticket->getOrganisation();
            $form->setData($ticket->getArrayCopy());
        } else {
            $organisation = $this->ticketService->getOrganisationByUuid($request->getAttribute('org_id'));
        }

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                $data['agent_id']        = $agentId;
                $data['organisation_id'] = $organisation->getId();
                $ticket                  = $this->ticketService->save($data);

                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('info', sprintf('Ticket #%s successfully created', $ticket->getId()));

                return new RedirectResponse($this->urlHelper->generate('ticket.list'));
            }
        }

        $sites = $this->ticketService->getSitesByOrganisationId($organisation->getId());
        $this->setFormSiteOptions($form, $sites);

        $contacts = $this->ticketService->getContactsByOrganisationId($organisation->getId());
        $this->setFormContactOptions($form, $contacts);

        $queues = $this->ticketService->getQueues();
        $this->setFormQueueOptions($form, $queues);

        return new HtmlResponse($this->renderer->render('ticket::create-ticket', [
            'form'   => $form,
            'org_id' => $organisation->getUuid(),
        ]));
    }

    /**
     * @param FormInterface $form form to populate
     * @param SiteEntity[] $sites
     */
    private function setFormSiteOptions(FormInterface $form, array $sites = []): void
    {
        // no sites? nothing to do
        if (empty($sites)) {
            return;
        }

        foreach ($sites as $site) {
            $siteOptions[$site->getId()] = $site->getAddressAsString();
        }
        $form->get('site_id')->setValueOptions($siteOptions);
        if (count($sites) === 1) {
            $form->get('site_id')->setValue($sites[0]->getId());
        }
    }

    private function setFormContactOptions(FormInterface $form, array $contacts = []): void
    {
        // no contacts, nothing to do
        if (empty($contacts)) {
            return;
        }

        foreach ($contacts as $contact) {
            $siteOptions[$contact->getId()] = sprintf("%s %s", $contact->getFirstName(), $contact->getLastName());
        }
        $form->get('contact_id')->setValueOptions($siteOptions);
        if (count($contacts) === 1) {
            $form->get('contact_id')->setValue($contacts[0]->getId());
        }
    }

    private function setFormQueueOptions(FormInterface $form, array $queues = []): void
    {
        // no contacts, nothing to do
        if (empty($queues)) {
            return;
        }

        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $siteOptions[$queue->getId()] = $queue->getName();
        }

        $form->get('queue_id')->setValueOptions($siteOptions);
        if (count($queues) === 1) {
            $form->get('queue_id')->setValue($queues[0]->getId());
        }
    }
}
