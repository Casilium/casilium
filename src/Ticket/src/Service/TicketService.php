<?php

declare(strict_types=1);

namespace Ticket\Service;

use Doctrine\ORM\EntityManager;
use Laminas\Validator\Timezone;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Service\ContactService;
use OrganisationSite\Entity\SiteEntity;
use OrganisationSite\Service\SiteManager;
use Ticket\Entity\Priority;
use Ticket\Entity\Queue;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\Type;
use User\Entity\User;
use User\Service\UserManager;

class TicketService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var OrganisationManager
     */
    protected $organisationManager;

    /**
     * @var SiteManager
     */
    protected $siteManager;

    /**
     * @var ContactService
     */
    protected $contactManager;

    /**
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * @var UserManager
     */
    protected $userManager;

    public function __construct(
        EntityManager $entityManager,
        OrganisationManager $organisationManager,
        SiteManager $siteManager,
        ContactService $contactService,
        QueueManager $queueManager,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->organisationManager = $organisationManager;
        $this->siteManager = $siteManager;
        $this->contactManager = $contactService;
        $this->queueManager = $queueManager;
        $this->userManager = $userManager;
    }

    /**
     * Find organisation by uuid
     *
     * @param string $uuid
     * @return Organisation
     */
    public function getOrganisationByUuid(string $uuid): Organisation
    {
        return $this->organisationManager->findOrganisationByUuid($uuid);
    }

    /**
     * Find organisation by id
     *
     * @param int $id
     * @return Organisation
     */
    public function getOrganisationById(int $id): Organisation
    {
        return $this->organisationManager->findOrganisationById($id);
    }

    /**
     * Find site by id
     *
     * @param int $id
     * @return SiteEntity
     */
    public function findSiteById(int $id): SiteEntity
    {
        return $this->siteManager->fetchSiteById($id);
    }

    /**
     * Find sites by organisation id (sites belonging to organisation)
     *
     * @param int $id
     * @return array|null
     */
    public function getSitesByOrganisationId(int $id): ?array
    {
        return $this->siteManager->fetchSitesByOrganisationId($id);
    }

    /**
     * Return contacts by organisation id (contacts belonging to organisation)
     *
     * @param int $id
     * @return array
     */
    public function getContactsByOrganisationId(int $id): array
    {
        return $this->contactManager->fetchContactsByOrganisationId($id);
    }

    /**
     * Find contact by id
     *
     * @param int $id
     * @return Contact|null
     */
    public function findContactById(int $id): ?Contact
    {
        return $this->contactManager->findContactById($id);
    }

    /**
     * Find queue by id
     *
     * @param int $id
     * @return Queue
     */
    public function findQueueById(int $id): Queue
    {
        return $this->queueManager->findQueueById($id);
    }

    /**
     * Find user by id
     *
     * @param int $id
     * @return User
     */
    public function findUserById(int $id): User
    {
        return $this->userManager->findById($id);
    }

    /**
     * Find ticket priority by id
     *
     * @param int $id
     * @return Priority
     */
    public function findPriorityById(int $id): Priority
    {
        return $this->entityManager->getRepository(Priority::class)->find($id);
    }

    /**
     * Return ticket type by id
     *
     * @param int $id
     * @return Type
     */
    public function findTypeById(int $id): Type
    {
        return $this->entityManager->getRepository(Type::class)->find($id);
    }

    public function findStatusById(int $id): Status
    {
        return $this->entityManager->getRepository(Status::class)->find($id);
    }

    /**
     * Retrieve ticket list
     *
     * @param array $options
     * @return array
     */
    public function fetchAllTickets($options = []): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findAll();
    }

    /**
     * Save ticket
     *
     * @param array $data
     * @return Ticket
     */
    public function save(array $data): Ticket
    {
        $ticket = new Ticket();

        $ticket->setShortDescription($data['short_description']);
        $ticket->setLongDescription($data['long_description']);
        $ticket->setImpact($data['impact']);
        $ticket->setUrgency($data['urgency']);
        $ticket->setSource($data['source']);

        $priority = $this->findPriorityById(($ticket->getImpact() + $ticket->getUrgency()));
        $ticket->setPriority($priority);

        $queue = $this->findQueueById($data['queue_id']);
        $ticket->setQueue($queue);


        $organisation = $this->getOrganisationById($data['organisation_id']);
        $ticket->setOrganisation($organisation);

        $site = $this->findSiteById($data['site_id']);
        $ticket->setSite($site);

        $agent = $this->entityManager->getRepository(User::class)
            ->find($data['agent_id']);
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

    /**
     * Return list of queues
     *
     * @return array
     */
    public function getQueues(): array
    {
        return $this->queueManager->findAll();
    }
}