<?php

declare(strict_types=1);

namespace Ticket\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use Ticket\Entity\Priority;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\Type;
use Ticket\Service\TicketService;

class TicketHydrator extends AbstractHydrator
{
    /**
     * @var TicketService
     */
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * @param array $data
     * @param object|Ticket $object
     * @return object|void
     */
    public function hydrate(array $data, object $object)
    {
        // set ticket contact
        $object->setContact($this->ticketService->findContactById($data['contact_id']));
        $object->setSite($this->ticketService->findSiteById($data['site_id']));
        //$object->setOrganisation($this->ticketService->getOrganisationById($data['organisation_id']));
        $object->setQueue($this->ticketService->findQueueById($data['queue_id']));
        $object->setAgent($this->ticketService->findUserById($data['agent_id']));

        $object->setShortDescription($data['short_description']);
        $object->setLongDescription($data['long_description']);
        $object->setSource($data['source']);

        // set ticket type (incident/problem/etc)
        $ticketType = new Type();
        $ticketType->setId($data['type_id']);
        $ticketType->setDescription($ticketType::getStatusTextFromId($data['type_id']));
        $object->setType($ticketType);

        // set ticket status
        $status = new Status();
        $status->setId($data['status_id'] ?? 1);
        $status->setDescription($status::getStatusTextFromId($status->getId()));
        $object->setStatus($status);

        // Get urgency and impact
        $object->setImpact((int)$data['impact']);
        $object->setUrgency((int)$data['urgency']);

        // Set priority (impact + urgency)
        $priority = new Priority();
        $priority->setId($object->getImpact() + $object->getUrgency());
        $priority->setName($priority::getPriorityDescription($priority->getId()));
        $object->setPriority($priority);

        return $object;
    }

    public function extract(object $object): array
    {
        return [];
    }
}