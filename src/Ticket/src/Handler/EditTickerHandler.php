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

use function sprintf;

class EditTickerHandler implements RequestHandlerInterface
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
        $ticketUuid = $request->getAttribute('ticket_id');
        $ticket     = $this->ticketService->getTicketByUuid($ticketUuid);

        $form = new TicketForm();
        $form->get('agent_id')->setValue($ticket->getAgent()->getId());
        $form->get('short_description')->setValue($ticket->getShortDescription());
        $form->get('long_description')->setValue($ticket->getLongDescription());
        $form->get('source')->setValue($ticket->getSource());
        $form->get('contact_id')->setValue($ticket->getContact()->getId());
        $form->get('queue_id')->setValue($ticket->getQueue()->getId());
        $form->get('site_id')->setValue($ticket->getSite()->getId());
        $form->get('impact')->setValue($ticket->getImpact());
        $form->get('urgency')->setValue($ticket->getUrgency());
        //$form->get('priority_id')->setValue($ticket->getPriority()->getId());
        $form->get('type_id')->setValue($ticket->getType()->getId());
        $form->get('due_date')->setValue($ticket->getDueDate());

        if ($request->getMethod() === 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $data = $form->getData();

                $data['id']              = $ticket->getId();
                $data['agent_id']        = $ticket->getAgent()->getId();
                $data['organisation_id'] = $ticket->getOrganisation()->getId();
                $ticket                  = $this->ticketService->save($data);

                $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
                $flashMessages->flash('info', sprintf('Ticket #%s successfully created', $ticket->getId()));

                return new RedirectResponse($this->urlHelper->generate('organisation.list'));
            }
        }

        $sites = $this->ticketService->getSitesByOrganisationId($ticket->getOrganisation()->getId());
        $this->setFormSiteOptions($form, $sites);

        $contacts = $this->ticketService->getContactsByOrganisationId($ticket->getOrganisation()->getId());
        $this->setFormContactOptions($form, $contacts);

        $queues = $this->ticketService->getQueues();
        $this->setFormQueueOptions($form, $queues);

        return new HtmlResponse($this->renderer->render('ticket::create-ticket', [
            'form' => $form,
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
    }
}
