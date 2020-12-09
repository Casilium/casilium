<?php

declare(strict_types=1);

namespace Ticket\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\Event;
use Logger\Service\LogService;
use MailService\Service\MailService;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;

class TicketEventListener
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var MailService */
    protected $mailService;

    /** @var Logger */
    protected $logger;

    /** @var bool  */
    protected $enabled = false;

    public function __invoke(ContainerInterface $container): TicketEventListener
    {
        $config        = $container->get('config')['mail'] ?? [];
        $entityManager = $container->get(EntityManager::class);
        $logService    = $container->get(LogService::class);
        $mailService   = $container->get(MailService::class);

        $listener = new self();
        $listener->setEntityManager($entityManager);
        $listener->setLogService($logService);
        $listener->setMailService($mailService);
        $listener->setEnabled($config['enabled'] ?? false);

        return $listener;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setMailService(MailService $mailService): void
    {
        $this->mailService = $mailService;
    }

    public function setLogService(Logger $logService): void
    {
        $this->logger = $logService;
    }

    /**
     * Event called when ticket is created.
     *
     * @param Event $event ticket created event
     */
    public function onTicketCreated(Event $event): void
    {
        // if mail service is disabled, don't send mail
        if (false === $this->enabled) {
            return;
        }

        $ticketId = $event->getParam('id') ?? null;

        if (null !== $ticketId) {
            /** @var Ticket $ticket */
            $ticket = $this->entityManager->getRepository(Ticket::class)->find($ticketId);

            $body = $this->mailService->prepareBody('ticket_mail::ticket_created', ['ticket' => $ticket]);
            $this->mailService->send(
                $ticket->getContact()->getWorkEmail(),
                'Support Case Open',
                $body
            );
        }
    }

    /**
     * Event called when a reply is created.
     *
     * @param Event $event ticket event
     */
    public function onTicketReply(Event $event): void
    {
        // if mail service is disabled, don't send mail
        if (false === $this->enabled) {
            return;
        }

        // if we have no respond ID, return
        $responseId = $event->getParam('id') ?? null;
        if (null === $responseId) {
            return;
        }

        /** @var TicketResponse $response */
        $response = $this->entityManager->getRepository(TicketResponse::class)->find($responseId);

        // if response is not public, don't send an email
        if ($response->getIsPublic() === 0) {
            return;
        }

        //response is not from agent,
        if (null === $response->getAgent()) {
            return;
        }

        // default template is ticket response
        $template = "ticket_mail::ticket_response";
        if ($response->getTicket()->getStatus()->getId() === Status::STATUS_RESOLVED) {
            // use resolved template
            $template = "ticket_mail::ticket_resolved";
        }

        // prepare email body and send email
        $body = $this->mailService->prepareBody($template, ['response' => $response]);
        $this->mailService->send(
            $response->getContact()->getWorkEmail(),
            'Your request has been updated',
            $body
        );
    }

    /**
     * Set status of mail service, if mail is disabled no email will be sent.
     *
     * @param bool $flag is mail service disabled
     */
    public function setEnabled(bool $flag = true): void
    {
        $this->enabled = $flag;
    }
}
