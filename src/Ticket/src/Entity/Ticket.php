<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use OrganisationSite\Entity\SiteEntity;
use Ramsey\Uuid\Uuid;
use ServiceLevel\Entity\SlaTarget;
use ServiceLevel\Service\CalculateBusinessHours;
use User\Entity\User;

use function date;
use function get_object_vars;
use function is_string;
use function strlen;

/**
 * @ORM\Entity(repositoryClass="Ticket\Repository\TicketRepository")
 * @ORM\Table(name="ticket")
 */
class Ticket
{
    public const IMPACT_HIGH    = 1;
    public const IMPACT_MEDIUM  = 2;
    public const IMPACT_LOW     = 3;
    public const IMPACT_DEFAULT = self::IMPACT_LOW;

    public const SOURCE_EMAIL = 1;
    public const SOURCE_PHONE = 2;
    public const SOURCE_WEB   = 3;

    public const STATUS_NEW         = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_ON_HOLD     = 3;
    public const STATUS_RESOLVED    = 4;
    public const STATUS_CLOSED      = 5;
    public const STATUS_CANCELLED   = 6;
    public const STATUS_DEFAULT     = self::STATUS_NEW;

    public const URGENCY_HIGH    = 1;
    public const URGENCY_MEDIUM  = 2;
    public const URGENCY_LOW     = 3;
    public const URGENCY_DEFAULT = self::URGENCY_LOW;

    private const IMPACT_URGENCY_TEXT = [
        self::IMPACT_HIGH   => 'High',
        self::IMPACT_MEDIUM => 'Medium',
        self::IMPACT_LOW    => 'Low',
    ];

    private const SOURCE_TEXT = [
        self::SOURCE_EMAIL => 'E-Mail',
        self::SOURCE_PHONE => 'Phone',
        self::SOURCE_WEB   => 'Web',
    ];

    private const STATUS_TEXT = [
        self::STATUS_NEW         => 'Open',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_ON_HOLD     => 'On Hold',
        self::STATUS_RESOLVED    => 'Resolved',
        self::STATUS_CLOSED      => 'Closed',
    ];

    /**
     * @ORM\OneToOne(targetEntity="User\Entity\User")
     * @ORM\JoinColumn(name="assigned_agent_id", referencedColumnName="id", nullable=true)
     */
    private User $assignedAgent;

    /** @ORM\Column(name="created_at", type="string", length=10) */
    private string $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="Agent", cascade={"all"})
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", nullable=true)
     */
    private Agent $agent;

    /**
     * @ORM\OneToOne(targetEntity="\OrganisationContact\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private Contact $contact;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /** @ORM\Column(name="impact", type="integer") */
    private int $impact;

    /** @ORM\Column(name="long_description", type="string") */
    private string $longDescription;

    /**
     * @ORM\OneToOne(targetEntity="\Organisation\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private Organisation $organisation;

    /**
     * @ORM\OneToOne(targetEntity="\Ticket\Entity\Priority")
     * @ORM\JoinColumn(name="priority_id", referencedColumnName="id")
     */
    private Priority|int $priority;

    /**
     * @ORM\OneToOne(targetEntity="\Ticket\Entity\Queue")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id")
     */
    private Queue $queue;

    /**
     * @ORM\OneToOne(targetEntity="\OrganisationSite\Entity\SiteEntity")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true)
     */
    private SiteEntity $site;

    /** @ORM\Column(name="short_description", type="string", length=255) */
    private string $shortDescription;

    /** @ORM\Column(name="source_id", type="integer") */
    private int $source;

    /** @ORM\Column(name="due_date", type="string") */
    private string $dueDate;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    private Status $status;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private Type $type;

    /** @ORM\Column(name="urgency", type="integer") */
    private int $urgency;

    /**
     * Unique ticket identifier, non-user friendly to use in e-mail messages to identify tickets
     *
     * @ORM\Column(name="uuid", type="string")
     */
    private string $uuid;

    /** @ORM\Column(name="last_response_date", type="string") */
    private ?string $lastResponseDate;

    /** @ORM\Column(name="resolve_date", type="string") */
    private ?string $resolveDate;

    /** @ORM\Column(name="first_response_date", type="string") */
    private ?string $firstResponseDate;

    /** @ORM\Column(name="first_response_due", type="string") */
    private ?string $firstResponseDue;

    /** @ORM\Column(name="last_notified", type="string") */
    private ?string $lastNotified;

    /** @ORM\Column(name="close_date", type="string") */
    private ?string $closeDate;

    /** @ORM\Column(name="waiting_date", type="string") */
    private ?string $waitingDate;

    /** @ORM\Column(name="waiting_reset_date", type="string") */
    private ?string $waitingResetDate;

    /**
     * @ORM\OneToOne(targetEntity="ServiceLevel\Entity\SlaTarget")
     * @ORM\JoinColumn(name="sla_target_id", referencedColumnName="id")
     */
    private ?SlaTarget $slaTarget = null;

    public function __construct()
    {
        $this->impact  = self::IMPACT_DEFAULT;
        $this->source  = self::SOURCE_PHONE;
        $this->urgency = self::URGENCY_DEFAULT;
        $this->uuid    = Uuid::uuid4()->toString();

        $this->priority = Priority::PRIORITY_LOW;

        $dateTime           = new DateTime('now', new DateTimeZone('UTC'));
        $this->createdAt    = $dateTime->format('Y-m-d H:i:s');
        $this->lastNotified = $dateTime->format('Y-m-d H:i:s');
    }

    public function getAssignedAgent(): ?User
    {
        return $this->assignedAgent;
    }

    public function setAssignedAgent(User $assignedAgent): Ticket
    {
        $this->assignedAgent = $assignedAgent;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): Ticket
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $user): Ticket
    {
        $this->agent = $user;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Ticket
    {
        $this->id = $id;
        return $this;
    }

    public function getImpact(): int
    {
        return $this->impact;
    }

    public function setImpact(int $impact): Ticket
    {
        $this->impact = $impact;
        return $this;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): Ticket
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): Ticket
    {
        $this->longDescription = $longDescription;
        return $this;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): Ticket
    {
        $this->priority = $priority;
        return $this;
    }

    public function getSource(): int
    {
        return $this->source;
    }

    public function setSource(int $source): Ticket
    {
        $this->source = $source;
        return $this;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    public function setDueDate(string $dueDate): Ticket
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): Ticket
    {
        $this->status = $status;
        return $this;
    }

    public function getUrgency(): int
    {
        return $this->urgency;
    }

    public function setUrgency(int $urgency): Ticket
    {
        $this->urgency = $urgency;
        return $this;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): Ticket
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function setQueue(Queue $queue): Ticket
    {
        $this->queue = $queue;
        return $this;
    }

    public function getSite(): ?SiteEntity
    {
        return $this->site;
    }

    public function setSite(SiteEntity $site): Ticket
    {
        $this->site = $site;
        return $this;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): Ticket
    {
        $this->type = $type;
        return $this;
    }

    public function getUuid(): string
    {
        if (is_string($this->uuid)) {
            return $this->uuid;
        }
        return $this->uuid->toString();
    }

    public function setUuid(string $uuid): Ticket
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): Ticket
    {
        $this->contact = $contact;
        return $this;
    }

    public function getLastResponseDate(): ?string
    {
        return $this->lastResponseDate;
    }

    public function setLastResponseDate(string $date): Ticket
    {
        $this->lastResponseDate = $date;
        return $this;
    }

    public function getFirstResponseDate(): ?string
    {
        return $this->firstResponseDate;
    }

    public function setFirstResponseDate(?string $firstResponseDate): Ticket
    {
        $this->firstResponseDate = $firstResponseDate;
        return $this;
    }

    public function getResolveDate(): ?string
    {
        return $this->resolveDate;
    }

    public function setResolveDate(?string $resolveDate): Ticket
    {
        $this->resolveDate = $resolveDate;
        return $this;
    }

    public function setSlaTarget(?SlaTarget $slaTarget): void
    {
        $this->slaTarget = $slaTarget;
    }

    public function getSlaTarget(): ?SlaTarget
    {
        return $this->slaTarget;
    }

    public function hasSla(): bool
    {
        if ($this->slaTarget !== null) {
            return true;
        }
        return false;
    }

    public function getResponseDueDate(): CarbonInterface
    {
        $businessHours     = $this->organisation->getSla()->getBusinessHours();
        $businessHoursCalc = new CalculateBusinessHours($businessHours);
        $timeCreated       = Carbon::parse($this->getCreatedAt());
        return $businessHoursCalc->addHoursTo($timeCreated, $this->getSlaTarget()->getResponseTime());
    }

    public function getLastNotified(): ?string
    {
        return $this->lastNotified;
    }

    public function setLastNotified(?string $lastNotified): Ticket
    {
        $this->lastNotified = $lastNotified;
        return $this;
    }

    public function getCloseDate(): ?string
    {
        return $this->closeDate;
    }

    public function getFirstResponseDue(): ?string
    {
        return $this->firstResponseDue;
    }

    public function setFirstResponseDue(?string $firstResponseDue): Ticket
    {
        $this->firstResponseDue = $firstResponseDue;
        return $this;
    }

    public function setCloseDate(string $closeDate): Ticket
    {
        $this->closeDate = $closeDate;
        return $this;
    }

    public function getWaitingDate(): ?string
    {
        return $this->waitingDate;
    }

    public function setWaitingDate(?string $waitingDate): Ticket
    {
        $this->waitingDate = $waitingDate;
        return $this;
    }

    public function getWaitingResetDate(): ?string
    {
        return $this->waitingResetDate;
    }

    public function setWaitingResetDate(?string $waitingResetDate): Ticket
    {
        $this->waitingResetDate = $waitingResetDate;
        return $this;
    }

    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    public function exchangeArray(array $data): Ticket
    {
        $this->id               = isset($data['id']) ? (int) $data['id'] : null;
        $this->createdAt        = isset($data['createdAt']) ? (string) $data['createdAt'] : date('Y-m-d H:i:s');
        $this->impact           = isset($data['impact']) ? (int) $data['impact'] : self::IMPACT_DEFAULT;
        $this->urgency          = isset($data['urgency']) ? (int) $data['urgency'] : self::URGENCY_DEFAULT;
        $this->shortDescription = isset($data['short_description']) ? (string) $data['short_description'] : null;
        $this->slaTarget        = $data['sla_target'] ?? null;
        $this->organisation     = $data['organisation'] ?? null;
        $this->longDescription  = isset($data['long_description']) ? (string) $data['long_description'] : null;

        $this->dueDate = isset($data['due_date']) && strlen($data['due_date']) > 1
            ? (string) $data['due_date']
            : date('Y-m-d H:i:s');

        $this->lastResponseDate = isset($data['last_response_date']) && strlen($data['last_response_date']) > 1
            ? (string) $data['last_response_date']
            : date('Y-m-d H:i:s');

        $this->lastResponseDate = isset($data['first_response_date']) && strlen($data['first_response_date']) > 1
            ? (string) $data['first_response_date']
            : date('Y-m-d H:i:s');

        $this->waitingDate = isset($data['waiting_date']) && strlen($data['waiting_date']) > 1
            ? (string) $data['waiting_date']
            : null;

        $this->waitingDate = isset($data['waiting_reset_date']) && strlen($data['waiting_reset_date']) > 1
            ? (string) $data['waiting_reset_date']
            : null;

        return $this;
    }

    public static function getStatusTextFromCode(int $code): string
    {
        return self::STATUS_TEXT[$code];
    }

    public static function getImpactUrgencyText(int $code): string
    {
        return self::IMPACT_URGENCY_TEXT[$code];
    }

    public static function getSourceTextFromCode(int $code): string
    {
        return self::SOURCE_TEXT[$code];
    }

    public function isOverdue(): bool
    {
        if (null === $this->getDueDate()) {
            return false;
        }

        $now = Carbon::now('UTC');
        $due = Carbon::createFromFormat('Y-m-d H:i:s', $this->getDueDate(), 'UTC');

        if ($due < $now) {
            return true;
        }

        return false;
    }
}
