<?php
declare(strict_types=1);

namespace Ticket\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use OrganisationSite\Entity\SiteEntity;
use Ramsey\Uuid\Uuid;
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
     *
     * @var User
     */
    private $assigned_agent;

    /**
     * @ORM\Column(name="created_at", type="string", length=10)
     *
     * @var string
     */
    private $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="Agent", cascade={"all"})
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", nullable=true)
     *
     * @var Agent
     */
    private $agent;

    /**
     * @ORM\OneToOne(targetEntity="\OrganisationContact\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     *
     * @var Contact
     */
    private $contact;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="impact", type="integer")
     *
     * @var int
     */
    private $impact;

    /**
     * @ORM\Column(name="long_description", type="string")
     *
     * @var string
     */
    private $long_description;

    /**
     * @ORM\OneToOne(targetEntity="\Organisation\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     *
     * @var Organisation
     */
    private $organisation;

    /**
     * @ORM\OneToOne(targetEntity="\Ticket\Entity\Priority")
     * @ORM\JoinColumn(name="priority_id", referencedColumnName="id")
     *
     * @var Priority
     */
    private $priority;

    /**
     * @ORM\OneToOne(targetEntity="\Ticket\Entity\Queue")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id")
     *
     * @var Queue
     */
    private $queue;

    /**
     * @ORM\OneToOne(targetEntity="\OrganisationSite\Entity\SiteEntity")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true)
     *
     * @var SiteEntity
     */
    private $site;

    /**
     * @ORM\Column(name="short_description", type="string", length=255)
     *
     * @var string
     */
    private $short_description;

    /**
     * @ORM\Column(name="source_id", type="integer")
     *
     * @var int
     */
    private $source;

    /**
     * @ORM\Column(name="start_date", type="string")
     *
     * @var string
     */
    private $start_date;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     *
     * @var Status
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     *
     * @var Type
     */
    private $type;

    /**
     * @ORM\Column(name="urgency", type="integer")
     *
     * @var int
     */
    private $urgency;

    /**
     * Unique ticket identifier, non-user friendly to use in e-mail messages to identify tickets
     *
     * @ORM\Column(name="uuid", type="string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @ORM\Column(name="last_response_date", type="string")
     *
     * @var string|null
     */
    private $lastResponseDate;

    public function __construct()
    {
        $this->impact  = self::IMPACT_DEFAULT;
        $this->source  = self::SOURCE_PHONE;
        $this->urgency = self::URGENCY_DEFAULT;
        $this->uuid    = Uuid::uuid4();

        $dateTime         = new DateTime('now', new DateTimeZone('UTC'));
        $this->createdAt  = $dateTime->format('Y-m-d H:i:s');
        $this->start_date = $this->createdAt;
    }

    public function getAssignedAgent(): ?User
    {
        return $this->assigned_agent;
    }

    public function setAssignedAgent(User $assigned_agent): Ticket
    {
        $this->assigned_agent = $assigned_agent;
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
        return $this->short_description;
    }

    public function setShortDescription(string $short_description): Ticket
    {
        $this->short_description = $short_description;
        return $this;
    }

    public function getLongDescription(): string
    {
        return $this->long_description;
    }

    public function setLongDescription(string $long_description): Ticket
    {
        $this->long_description = $long_description;
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

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function setStartDate(string $start_date): Ticket
    {
        $this->start_date = $start_date;
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

    public function getSite(): SiteEntity
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

    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    public function exchangeArray(array $data): Ticket
    {
        $this->id                = isset($data['id']) ? (int) $data['id'] : null;
        $this->createdAt         = isset($data['createdAt']) ? (string) $data['createdAt'] : date('Y-m-d H:i:s');
        $this->impact            = isset($data['impact']) ? (int) $data['impact'] : self::IMPACT_DEFAULT;
        $this->urgency           = isset($data['urgency']) ? (int) $data['urgency'] : self::URGENCY_DEFAULT;
        $this->short_description = isset($data['short_description']) ? (string) $data['short_description'] : null;
        $this->start_date        = isset($data['start_date']) && strlen($data['start_date']) > 1 ? (string) $data['start_date'] : date('Y-m-d H:i:s');
        $this->long_description  = isset($data['long_description']) ? (string) $data['long_description'] : null;
        $this->start_date        = isset($data['last_response_date']) && strlen($data['last_response_date']) > 1 ? (string) $data['last_response_date'] : date('Y-m-d H:i:s');
        $this->organisation      = $data['organisation'] ?? null;
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
}
