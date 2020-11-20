<?php

declare(strict_types=1);

namespace Ticket\Service;

use Doctrine\ORM\EntityManager;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use Ticket\Entity\Agent;
use Ticket\Entity\Priority;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;
use Ticket\Entity\Type;
use User\Entity\User;
use User\Service\UserManager;

class TicketService
{
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

    public function __construct(
        EntityManager $entityManager,
        OrganisationManager $organisationManager,
        SiteManager $siteManager,
        ContactService $contactService,
        QueueManager $queueManager,
        UserManager $userManager
    ) {
        $this->entityManager       = $entityManager;
        $this->organisationManager = $organisationManager;
        $this->siteManager         = $siteManager;
        $this->contactManager      = $contactService;
        $this->queueManager        = $queueManager;
        $this->userManager         = $userManager;
    }

    /**
     * Find organisation by uuid
     */
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
    public function findSiteById(int $id): SiteEntity
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

    public function findTicketById(int $id) {
        return $this->entityManager->getRepository(Ticket::class)->find($id);
    }

    /**
     * Retrieve ticket list
     *
     * @return array
     */
    public function fetchAllTickets(): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findAll();
    }

    public function findTicketsByOrganisationUuid(string $uuid)
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

        $site = $this->findSiteById($data['site_id']);
        $ticket->setSite($site);

        $agent = $this->entityManager->getRepository(Agent::class)->find($data['agent_id']);
        $ticket->setAgent($agent);

        $contact = $this->contactManager->findContactById($data['contact_id']);
        $ticket->setContact($contact);

        $start_date = $data['start_date'] ?? null;
        if (! empty($start_date)) {
            $ticket->setStartDate($start_date);
        }

        $type = $this->findTypeById($data['type_id']);
        $ticket->setType($type);

        $status = $data['status'] ?? null;
        if (empty($status)) {
            $ticket->setStatus($this->findStatusById(1));
        }

        return $this->entityManager->getRepository(Ticket::class)->save($ticket);
    }

    public function updateStatus(int $id, int $state, $fromResponse = true): Status
    {
        /** @var Status $status */
        $status = $this->findStatusById($state);

        /** @var Ticket $ticket */
        $ticket = $this->findTicketById($id);
        $ticket->setStatus($status);

        $dt = new \DateTime('now');
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
     * @return TicketResponse
     */
    public function saveResponse(Ticket $ticket, array $data): TicketResponse
    {
        // update ticket
        $ticketStatus = $ticket->getStatus();
        switch ($data['submit']) {
            case 'save_hold':
                if ($ticket->getStatus()->getId() !== Status::STATUS_ON_HOLD) {
                    // if not on hold, place on hold
                    $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_ON_HOLD);
                } else {
                    // if on hold, place in progress.
                    $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_IN_PROGRESS);
                }
                break;
            case 'save_resolve':
                $ticketStatus = $this->updateStatus($ticket->getId(), Status::STATUS_RESOLVED);
                break;
            default:
                break;
        }

        $response = new TicketResponse();
        $ticket->setStatus($ticketStatus);
        $response->setTicket($ticket);
        $response->setResponse($data['response']);
        $response->setIsPublic($data['is_public']);
        $response->setContact($ticket->getContact());
        $response->setTicketStatus($ticket->getStatus());

        // find current user and set as agent responding to ticket
        $agent = $this->entityManager->getRepository(Agent::class)->find($data['agent_id']);
        $response->setAgent($agent);

        // save ticket response;
        $this->entityManager->persist($response);
        $this->entityManager->flush();

        return $response;
    }

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
}
