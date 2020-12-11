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
use SlackIntegration\Service\Client as slackClient;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;
use function sprintf;

class TicketEventListener
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var MailService */
    protected $mailService;

    /** @var slackClient */
    protected $slackClient;

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
        $slackClient   = $container->get(slackClient::class);

        $listener = new self();
        $listener->setEntityManager($entityManager);
        $listener->setLogService($logService);
        $listener->setMailService($mailService);
        $listener->setEnabled($config['enabled'] ?? false);
        $listener->setSetSlackClient($slackClient);

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
        $ticketId = $event->getParam('id') ?? null;

        if (null !== $ticketId) {
            /** @var Ticket $ticket */
            $ticket = $this->entityManager->getRepository(Ticket::class)->find($ticketId);

            if ($this->enabled) {
                $body = $this->mailService->prepareBody('ticket_mail::ticket_created', ['ticket' => $ticket]);
                $this->mailService->send(
                    $ticket->getContact()->getWorkEmail(),
                    'Support Case Open',
                    $body
                );
            }

            if ($this->slackClient->isEnabled()) {
                $createdBy = null === $ticket->getAgent()
                    ? $ticket->getContact()->getFirstName() . ' ' . $ticket->getContact()->getLastName()
                    : $ticket->getAgent()->getFullName();

                $message = $this->slackClient->createMessage();
                $message->setText(sprintf(
                    'Ticket #%s was created by %s - %s',
                    $ticket->getId(),
                    $createdBy,
                    $ticket->getShortDescription()
                ));
                $this->slackClient->sendMessage($message);
            }
        }
    }

    /**
     * Event called when a reply is created.
     *
     * @param Event $event ticket event
     */
    public function onTicketReply(Event $event): void
    {
        $action = 'response';

        // if we have no respond ID, return
        $responseId = $event->getParam('id') ?? null;
        if (null === $responseId) {
            return;
        }

        /** @var TicketResponse $response */
        $response = $this->entityManager->getRepository(TicketResponse::class)->find($responseId);

        //response is not from agent,
        if (null === $response->getAgent()) {
            return;
        }

        // default template is ticket response
        $template = "ticket_mail::ticket_response";
        if ($response->getTicket()->getStatus()->getId() === Status::STATUS_RESOLVED) {
            // use resolved template
            $action   = 'resolved';
            $template = "ticket_mail::ticket_resolved";
        }

        if ($this->enabled === true && $response->getIsPublic() === 1) {
            // prepare email body and send email
            $body = $this->mailService->prepareBody($template, ['response' => $response]);
            $this->mailService->send(
                $response->getContact()->getWorkEmail(),
                'Your request has been updated',
                $body
            );
        }

        if ($this->slackClient->isEnabled()) {
            $updatedBy = null === $response->getAgent()
                ? $response->getContact()->getFirstName() . ' ' . $response->getContact()->getLastName()
                : $response->getAgent()->getFullName();

            $text = sprintf(
                'Ticket #%s %s by %s: %s',
                $response->getTicket()->getId(),
                $action,
                $updatedBy,
                $response->getResponse()
            );

            $message = $this->slackClient->createMessage();
            $message->setText($text);
            $this->slackClient->sendMessage($message);
        }
    }

    public function setSetSlackClient(slackClient $client): void
    {
        $this->slackClient = $client;
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
