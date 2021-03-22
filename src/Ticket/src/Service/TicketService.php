<?php

declare(strict_types=1);

namespace Ticket\Service;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\EventManagerInterface;
use MailService\Service\MailService;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use ServiceLevel\Service\CalculateBusinessHours;
use Ticket\Entity\Agent;
use Ticket\Entity\Priority;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;
use Ticket\Entity\Type;
use User\Entity\User;
use User\Service\UserManager;
use function filter_var;
use function gmdate;
use function sprintf;
use const FILTER_SANITIZE_STRING;

class TicketService
{
    public const DUE_PERIOD_MINUTES = 1;
    public const DUE_PERIOD_HOURS   = 2;
    public const DUE_PERIOD_DAYS    = 3;
    public const DUE_PERIOD_WEEKS   = 4;
    public const DUE_PERIOD_MONTHS  = 5;

    /** @var EventManagerInterface */
    protected $eventManager;

    /** @var EntityManager */
    protected $entityManager;

    /** @var OrganisationManager */
    protected $organisationManager;

    /** @var SiteManager */
    protected $siteManager;

    /** @var ContactService */
    protected $contactManager;

    /** @var QueueManager */
    protected $queueManager;

    /** @var UserManager */
    protected $userManager;

   /** @var MailService  */
    protected $mailService;

    public function __construct(
        EventManagerInterface $eventManager,
        EntityManager $entityManager,
        OrganisationManager $organisationManager,
        SiteManager $siteManager,
        ContactService $contactService,
        QueueManager $queueManager,
        UserManager $userManager,
        MailService $mailService
    ) {
        $this->eventManager        = $eventManager;
        $this->entityManager       = $entityManager;
        $this->organisationManager = $organisationManager;
        $this->siteManager         = $siteManager;
        $this->contactManager      = $contactService;
        $this->queueManager        = $queueManager;
        $this->userManager         = $userManager;
        $this->mailService         = $mailService;
    }

    public function getOrganisationByUuid(string $uuid): Organisation
    {
        return $this->organisationManager->findOrganisationByUuid($uuid);
    }

    /**
     * Find organisation by id
     */
    public function getOrganisationById(int $id): Organisation
    {
        return $this->organisationManager->findOrganisationById($id);
    }

    /**
     * Find site by id
     */
    public function findSiteById(int $id): ?SiteEntity
    {
        return $this->siteManager->fetchSiteById($id);
    }

    /**
     * Find sites by organisation id (sites belonging to organisation)
     *
     * @return array|null
     */
    public function getSitesByOrganisationId(int $id): ?array
    {
        return $this->siteManager->fetchSitesByOrganisationId($id);
    }

    /**
     * Return contacts by organisation id (contacts belonging to organisation)
     *
     * @return array
     */
    public function getContactsByOrganisationId(int $id): array
    {
        return $this->contactManager->fetchContactsByOrganisationId($id);
    }

    /**
     * Find contact by id
     */
    public function findContactById(int $id): ?Contact
    {
        return $this->contactManager->findContactById($id);
    }

    /**
     * Find queue by id
     */
    public function findQueueById(int $id): Queue
    {
        return $this->queueManager->findQueueById($id);
    }

    /**
     * Find user by id
     */
    public function findUserById(int $id): User
    {
        return $this->userManager->findById($id);
    }

    /**
     * Find ticket priority by id
     */
    public function findPriorityById(int $id): Priority
    {
        return $this->entityManager->getRepository(Priority::class)->find($id);
    }

    /**
     * Return ticket type by id
     */
    public function findTypeById(int $id): Type
    {
        return $this->entityManager->getRepository(Type::class)->find($id);
    }

    public function findStatusById(int $id): Status
    {
        return $this->entityManager->getRepository(Status::class)->find($id);
    }

    public function findTicketById(int $id): ?Ticket
    {
        return $this->entityManager->getRepository(Ticket::class)->find($id);
    }

    /**
     * Retrieve ticket list
     *
     * @param bool $fetchResolved whether to fetch resolved and closed tickets
     * @return array
     */
    public function fetchAllTickets(bool $fetchResolved = true): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findAll($fetchResolved);
    }

    public function findTicketsByOrganisationUuid(string $uuid): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findByOrganisationUuid($uuid);
    }

    /**
     * Save ticket
     *
     * @param array $data
     */
    public function save(array $data): Ticket
    {
        $id = $data['id'] ?? 0;
        if ($id === 0) {
            $ticket = new Ticket();
        } else {
            $ticket = $this->findTicketById($id);
        }

        $ticket->setShortDescription($data['short_description']);
        $ticket->setLongDescription($data['long_description']);
        $ticket->setImpact($data['impact']);
        $ticket->setUrgency($data['urgency']);
        $ticket->setSource($data['source']);

        $priority = $this->findPriorityById($ticket->getImpact() + $ticket->getUrgency());
        $ticket->setPriority($priority);

        $queue = $this->findQueueById($data['queue_id']);
        $ticket->setQueue($queue);

        $organisation = $this->getOrganisationById($data['organisation_id']);
        $ticket->setOrganisation($organisation);

        $siteId = isset($data['site_id']) ? (int) $data['site_id'] : 0;
        if ($siteId > 0) {
            $site = $this->findSiteById($data['site_id']);
            if ($site !== null) {
                $ticket->setSite($site);
            }
        }

        $agentId = $data['agent_id'] ?? null;
        if (null !== $agentId) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($data['agent_id']);
            $ticket->setAgent($agent);
        }

        $contact = $this->contactManager->findContactById($data['contact_id']);
        $ticket->setContact($contact);

        $type = $this->findTypeById($data['type_id']);
        $ticket->setType($type);

        // assign sla target if organisation has sla
        if (
            ($ticket->getType()->getId() === $ticket->getType()::TYPE_INCIDENT
                || $ticket->getType()->getId() === $ticket->getType()::TYPE_PROBLEM
            )
            && $organisation->hasSla()
        ) {
            $ticket->setSlaTarget($organisation->getSla()->getSlaTarget($ticket->getPriority()->getId()));
        }

        $dueDate = $data['due_date'] ?? null;
        if (! empty($dueDate)) {
            $ticket->setDueDate($dueDate);
        } else {
            $date = Carbon::now('UTC');

            if ($organisation->getSla() !== null) {
                $calc = new CalculateBusinessHours($organisation->getSla()->getBusinessHours());

                $result = $calc->addHoursTo(
                    $date,
                    $organisation->getSla()->getSlaTarget($priority->getId())->getResolveTime()
                );
                $ticket->setDueDate($result->format('Y-m-d H:i:s'));
            }
        }

        $firstResponseDue = $data['first_response_due'] ?? null;
        if (! empty($firstResponseDue)) {
            $ticket->setFirstResponseDue($firstResponseDue);
        } else {
            $date = Carbon::now('UTC');

            if ($organisation->getSla() !== null) {
                $calc   = new CalculateBusinessHours($organisation->getSla()->getBusinessHours());
                $result = $calc->addHoursTo(
                    $date,
                    $organisation->getSla()->getSlaTarget($priority->getId())->getResponseTime()
                );
                $ticket->setFirstResponseDue($result->format('Y-m-d H:i:s'));
            }
        }

        $status = $data['status'] ?? null;
        if (empty($status)) {
            $ticket->setStatus($this->findStatusById(1));
        }

        $ticket = $this->entityManager->getRepository(Ticket::class)->save($ticket);
        $this->eventManager->trigger('ticket.created', $this, ['id' => $ticket->getId()]);

        return $ticket;
    }

    public function updateStatus(int $id, int $state): Status
    {
        $status = $this->findStatusById($state);
        $ticket = $this->findTicketById($id);
        $ticket->setStatus($status);

        $dt = new DateTime('now');
        $ticket->setLastResponseDate($dt->format('Y-m-d H:i:s'));

        $this->entityManager->flush();

        return $status;
    }

    /**
     * Return list of queues
     *
     * @return array
     */
    public function getQueues(): array
    {
        return $this->queueManager->findAll();
    }

    public function getTicketByUuid(string $uuid): Ticket
    {
        return $this->entityManager->getRepository(Ticket::class)->findTicketByUuid($uuid);
    }

    /**
     * Save ticket response
     *
     * @param Ticket $ticket Ticket related to response
     * @param array $data response data
     */
    public function saveResponse(Ticket $ticket, array $data): TicketResponse
    {
        $this->entityManager->clear();

        $ticket       = $this->findTicketById($ticket->getId());
        $ticketStatus = $ticket->getStatus();

        $submitType = $data['submit'] ?? null;
        if ($submitType !== null) {
            switch ($data['submit']) {
                case 'save_hold':
                    if ($ticket->getStatus()->getId() !== Status::STATUS_ON_HOLD) {
                        // if not on hold, place on hold
                        $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_ON_HOLD);

                        // set hold date
                        $ticket->setWaitingDate(Carbon::now('UTC')->format('Y-m-d H:i:s'));
                    } else {
                        // if on hold, place in progress.
                        $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_IN_PROGRESS);

                        // remove waiting/hold date
                        $ticket->setWaitingDate(null);
                        $ticket->setWaitingResetDate(null);
                    }
                    break;
                case 'save_resolve':
                    $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_RESOLVED);
                    $ticket->setResolveDate(Carbon::now('UTC')->format('Y-m-d H:i:s'));
                    break;
                default:
                    // update ticket status to IN PROGRESS if is currently OPEN
                    if ($ticket->getStatus()->getId() === Status::STATUS_OPEN) {
                        $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_IN_PROGRESS);
                    }
                    break;
            }
        }
        /** @var TicketResponse[] $responses */
        $responses = $this->findTicketResponses($ticket->getId());
        if (empty($responses)) {
            $ticket->setFirstResponseDate(Carbon::now()->format('Y-m-d H:i:s'));
        }

        $ticket->setLastResponseDate(Carbon::now()->format('Y-m-d H:i:s'));

        $response = new TicketResponse();
        $ticket->setStatus($ticketStatus);
        $response->setTicket($ticket);
        $response->setResponse($data['response']);
        $response->setIsPublic($data['is_public']);
        $response->setContact($ticket->getContact());
        $response->setTicketStatus($ticket->getStatus());

        $agentId = $data['agent_id'] ?? null;
        if (null !== $agentId) {
            /** @var Agent $agent */
            $agent = $this->entityManager->getRepository(Agent::class)->find($agentId);
            $response->setAgent($agent);
        }

        // save ticket response;
        $this->entityManager->persist($response);
        $this->entityManager->flush();

        $this->eventManager->trigger('ticket.reply', $this, ['id' => $response->getId()]);
        return $response;
    }

    public function sendNotificationEmail(Ticket $ticket, int $target, int $period = self::DUE_PERIOD_MINUTES)
    {
        $now          = Carbon::now('UTC');
        $due          = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate());
        $lastNotified = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getLastNotified());

        $secondsDue = $due->diffInSeconds($now);

        $notifyAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate());
        switch ($period) {
            case self::DUE_PERIOD_MINUTES:
                $notifyAt->subMinutes($target);
                break;
            case self::DUE_PERIOD_HOURS:
                $notifyAt->subHours($target);
                break;
            case self::DUE_PERIOD_DAYS:
                $notifyAt->subDays($target);
                break;
            case self::DUE_PERIOD_WEEKS:
                $notifyAt->subWeeks($target);
                break;
            case self::DUE_PERIOD_MONTHS:
                $notifyAt->subMonths($target);
                break;
        }

        /*
        echo sprintf(
            "-- Due: %s, Notify at: %s, Last notified: %s\n",
            $due->format('Y-m-d H:i:s'),
            $notifyAt->format('Y-m-d H:i:s'),
            $lastNotified->format('Y-m-d H:i:s')
        );
        */

        if ($lastNotified < $notifyAt) {
            $due     = gmdate('H:i:s', $secondsDue);
            $subject = sprintf('Ticket %s due in %s', $ticket->getId(), $secondsDue);

            $body = sprintf(
                'Ticket %s raised by %s at %s is due in %s',
                $ticket->getId(),
                filter_var($ticket->getContact()->getFirstName(), FILTER_SANITIZE_STRING),
                filter_var($ticket->getOrganisation()->getName(), FILTER_SANITIZE_STRING),
                $due
            );

            /** @var Agent $agent */
            foreach ($ticket->getQueue()->getMembers() as $agent) {
                $this->mailService->send($agent->getEmail(), $subject, $body);
            }

            $ticket->setLastNotified($now->format('Y-m-d H:i:s'));
            $this->entityManager->flush();
        }
    }

    public function sendOverdueNotificationEmail(Ticket $ticket): void
    {
        $subject = sprintf('Ticket #%s is now overdue', $ticket->getId());
        $body    = sprintf(
            'Ticket #%s raised by %s from %s is now overdue and requires attention',
            $ticket->getId(),
            filter_var($ticket->getContact()->getFirstName(), FILTER_SANITIZE_STRING),
            filter_var($ticket->getOrganisation()->getName(), FILTER_SANITIZE_STRING),
        );

        /** @var Agent $member */
        foreach ($ticket->getQueue()->getMembers() as $member) {
            $this->mailService->send($member->getEmail(), $subject, $body);
        }

        $ticket->setLastNotified(Carbon::now('UTC')->format('Y-m-d H:i:s'));
        $this->entityManager->flush();
    }

    /**
     * @param int $id Ticket ID
     * @return array array of responses
     */
    public function findTicketResponses(int $id): array
    {
        return $this->entityManager->getRepository(TicketResponse::class)
            ->findTicketResponsesByTicketId($id);
    }

    public function findRecentTicketsByContact(int $id): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findRecentTicketsByContact($id);
    }

    public function findAgentFromId(int $id): Agent
    {
        return $this->entityManager->getRepository(Agent::class)->find($id);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function findTicketsDueWithin(int $target, int $period = self::DUE_PERIOD_MINUTES): array
    {
        return $this->entityManager->getRepository(Ticket::class)
            ->findTicketsDueWithin($target, $period);
    }

    public function newTicketReplyNotification(Ticket $ticket): void
    {
        $agents = $ticket->getQueue()->getMembers();

        $body = sprintf('A new reply has been posted to ticket #%s', $ticket->getId());

        /** @var Agent $agent */
        foreach ($agents as $agent) {
            $this->mailService->send($agent->getEmail(), 'New ticket reply', $body);
        }
    }

    public function newTicketNotification(Ticket $ticket): void
    {
        $agents = $ticket->getQueue()->getMembers();

        $body = sprintf('A new ticket has been created, ticket #%s', $ticket->getId());

        /** @var Agent $agent */
        foreach ($agents as $agent) {
            $this->mailService->send($agent->getEmail(), 'New ticket notification', $body);
        }
    }

    public function findWaitingTicketsToUpdate(): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findWaitingTicketsToUpdateById();
    }

    public function updateWaitingTickets(): array
    {
        // returns an array of IDs of tickets that requiring the due date updating
        // because the ticket is on hold/waiting and the waiting date
        $tickets = $this->findWaitingTicketsToUpdate();

        // if no tickets to process return
        if (empty($tickets)) {
            return [];
        }

        $updateStatus = [];

        foreach ($tickets as $ticketId) {
            // find the ticket to update
            $ticket = $this->findTicketById((int) $ticketId);

            // if due date or waiting date is null then continue
            if (null === $ticket->getDueDate() || null === $ticket->getWaitingDate()) {
                continue;
            }

            // don't bother with tickets already overdue
            if ($ticket->isOverdue()) {
                continue;
            }

            // get due date as carbon object
            $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getDueDate(), 'UTC');
            $wasDue  = $dueDate->clone();

            // get waiting date as carbon object
            $waitingDate = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getWaitingDate(), 'UTC');

            // get last reset date (if set) as carbon object
            $waitingResetDate = $ticket->getWaitingResetDate()
                ? Carbon::createFromFormat('Y-m-d H:i:s', $ticket->getWaitingResetDate())
                : null;

            // update the due date
            $now = Carbon::now('UTC');
            if (null !== $waitingResetDate) {
                // get the difference in minutes between last reset and now
                // add the difference to the due date
                $diffInMinutes = $now->diffInMinutes($waitingResetDate);

                // no point in updating if same
                if ($diffInMinutes === 0) {
                    continue;
                }

                // if has sla then calculate according to SLA hours
                if ($ticket->hasSla()) {
                    $businessHours     = $ticket->getOrganisation()->getSla()->getBusinessHours();
                    $businessHoursCalc = new CalculateBusinessHours($businessHours);
                    $dueDate           = $businessHoursCalc->addMinutesTo($dueDate, $diffInMinutes);
                } else {
                    // otherwise just update
                    $dueDate = $dueDate->addMinutes($diffInMinutes);
                }
            } else {
                // ticket has never been reset, get difference in seconds between
                // date ticket was put on hold and now, add the difference to the due date
                $diffInMinutes = $now->diffInMinutes($waitingDate);
                if ($diffInMinutes === 0) {
                    continue;
                }

                // if has sla then calculate according to SLA hours
                if ($ticket->hasSla()) {
                    $businessHours     = $ticket->getOrganisation()->getSla()->getBusinessHours();
                    $businessHoursCalc = new CalculateBusinessHours($businessHours);
                    $dueDate           = $businessHoursCalc->addMinutesTo($dueDate, $diffInMinutes);
                } else {
                    // otherwise just update
                    $dueDate = $dueDate->addMinutes($diffInMinutes);
                }
            }

            // update due date and set the last reset date to now
            $ticket->setDueDate($dueDate->format('Y-m-d H:i:s'));
            $ticket->setWaitingResetDate($now->format('Y-m-d H:i:s'));

            $updateStatus[$ticket->getId()] = [
                'was_due' => $wasDue->format('Y-m-d H:i:s'),
                'now_due' => $dueDate->format('Y-m-d H:i:s'),
            ];

            // write ticket changes
            $this->getEntityManager()->flush();
        }

        return $updateStatus;
    }
}
