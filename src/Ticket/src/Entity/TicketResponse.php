<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ticket\Entity\Ticket;
use User\Entity\User;

/**
 * Class TicketResponse
 * @package Ticke\Entity
 * @ORM\Entity(repositoryClass="Ticket\Entity\Repository\TicketResponseRepository")
 * @ORM\Table(name="ticket_response")
 */
class TicketResponse
{
    /**
     *  @ORM\Id
     *  @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="agent_id", type="integer", nullable=true)
     * @var int
     */
    private $agent_id;

    /**
     * @ORM\Column(name="contact_id", type="integer", nullable=true)
     * @var int
     */
    private $contact_id;


    /**
     * @ORM\Column(name="response", type="string")
     * @var string
     */
    private $response;

    /**
     * @ORM\Column(name="response_date", type="string")
     * @var string
     */
    private $response_date;

    /**
     * @ORM\ManyToOne(targetEntity="Ticket\Entity\Ticket", inversedBy="response")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id")
     * @var Ticket
     */
    private $ticket;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Status")
     * @ORM\JoinColumn(name="ticket_status", referencedColumnName="id")
     * @var Status
     */
    private $ticket_status;

    /**
     * @ORM\Column(name="is_public")
     * @var int
     */
    private $is_public;

    public function __construct()
    {
        $this->response_date = date('Y-m-d H:i:s', time());
        $this->is_public = 1;
    }

    /**
     * @return int
     */
    public function getAgentId(): ?int
    {
        return $this->agent_id;
    }

    /**
     * @param int $user_id
     * @return TicketResponse
     */
    public function setAgentId(int $user_id): TicketResponse
    {
        $this->agent_id = $user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeId(): ?int
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     * @return TicketResponse
     */
    public function setEmployeeId(int $contact_id): TicketResponse
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return TicketResponse
     */
    public function setId(int $id): TicketResponse
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string $response
     * @return TicketResponse
     */
    public function setResponse(string $response): TicketResponse
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseDate(): string
    {
        return $this->response_date;
    }

    /**
     * @param string $response_date
     * @return TicketResponse
     */
    public function setResponseDate(string $response_date): TicketResponse
    {
        $this->response_date = $response_date;
        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     * @return TicketResponse
     */
    public function setTicket(Ticket $ticket): TicketResponse
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @return Status
     */
    public function getTicketStatus(): Status
    {
        return $this->ticket_status;
    }

    /**
     * @param Status $ticket_status
     * @return TicketResponse
     */
    public function setTicketStatus(Status $ticket_status): TicketResponse
    {
        $this->ticket_status = $ticket_status;
        return $this;
    }

    /**
     * @return int
     */
    public function getisPublic(): int
    {
        return $this->is_public;
    }

    /**
     * @param int $is_public
     * @return TicketResponse
     */
    public function setIsPublic(int $is_public): TicketResponse
    {
        $this->is_public = $is_public;
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
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->response = isset($data['response']) ? (string) $data['response'] : null;
        $this->agent_id = isset($data['agent_id']) ? (int) $data['agent_id'] : null;
        $this->contact_id = isset($data['contact_id']) ? (int) $data['contact_id'] : null;
        $this->ticket = $data['ticket'] ?? null;
        $this->is_public = isset($data['is_public']) ? (int) $data['is_public'] : 0;
        return $this;
    }
}
