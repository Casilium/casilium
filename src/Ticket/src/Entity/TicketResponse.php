<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\ORM\Mapping as ORM;
use OrganisationContact\Entity\Contact;
use Ticket\Entity\Ticket;

use function date;
use function get_object_vars;
use function time;

/**
 * @ORM\Entity(repositoryClass="Ticket\Repository\TicketResponseRepository")
 * @ORM\Table(name="ticket_response")
 */
class TicketResponse
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="Agent")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", nullable=true)
     */
    private ?Agent $agent;

    /**
     * @ORM\OneToOne(targetEntity="\OrganisationContact\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private ?Contact $contact;

    /** @ORM\Column(name="response", type="string") */
    private string $response;

    /** @ORM\Column(name="response_date", type="string") */
    private string $responseDate;

    /**
     * @ORM\ManyToOne(targetEntity="Ticket\Entity\Ticket", inversedBy="response")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id")
     */
    private Ticket $ticket;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Status")
     * @ORM\JoinColumn(name="ticket_status", referencedColumnName="id")
     */
    private Status $ticketStatus;

    /**
     * @ORM\Column(name="is_public")
     *
     * @var int
     */
    private $isPublic;

    public function __construct()
    {
        $this->responseDate = date('Y-m-d H:i:s', time());
        $this->isPublic     = 1;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $user): TicketResponse
    {
        $this->agent = $user;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): TicketResponse
    {
        $this->contact = $contact;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): TicketResponse
    {
        $this->id = $id;
        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(string $response): TicketResponse
    {
        $this->response = $response;
        return $this;
    }

    public function getResponseDate(): string
    {
        return $this->responseDate;
    }

    public function setResponseDate(string $responseDate): TicketResponse
    {
        $this->responseDate = $responseDate;
        return $this;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): TicketResponse
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function getTicketStatus(): Status
    {
        return $this->ticketStatus;
    }

    public function setTicketStatus(Status $ticketStatus): TicketResponse
    {
        $this->ticketStatus = $ticketStatus;
        return $this;
    }

    public function getIsPublic(): int
    {
        return $this->isPublic;
    }

    public function setIsPublic(int $isPublic): TicketResponse
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    public function exchangeArray(array $data): TicketResponse
    {
        $this->id       = $data['id'] ?? null;
        $this->response = $data['response'] ?? null;
        $this->agent    = $data['agent_id'] ?? null;
        $this->contact  = $data['contact_id'] ?? null;
        $this->ticket   = $data['ticket'] ?? null;
        $this->isPublic = isset($data['is_public']) ? (int) $data['is_public'] : 0;
        return $this;
    }
}
