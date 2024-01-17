<?php

declare(strict_types=1);

namespace Organisation\Entity;

use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Organisation\Exception\OrganisationNameException;
use Organisation\Validator\OrganisationNameValidator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ServiceLevel\Entity\Sla;

/**
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
 *
 * @ORM\Entity(repositoryClass="Organisation\Repository\OrganisationRepository")
 * @ORM\Table(name="organisation")
 */
class Organisation implements OrganisationInterface
{
    public const STATE_INACTIVE = 0;
    public const STATE_ACTIVE   = 1;
    public const STATE_DISABLED = 2;

    public const TYPE_CLIENT   = 1;
    public const TYPE_SUPPLIER = 2;
    public const TYPE_BOTH     = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected ?int $id;

    /** @ORM\Column(type="uuid", unique=true) */
    protected UuidInterface $uuid;

    /** @ORM\Column(type="utcdatetime", nullable=false) */
    protected DateTime $created;

    /** @ORM\Column(name="is_active", type="integer", nullable=false) */
    protected int $isActive;

    /**
     * @ORM\OneToOne(targetEntity="ServiceLevel\Entity\Sla")
     * @ORM\JoinColumn(name="sla_id", referencedColumnName="id", nullable=true)
     */
    protected ?Sla $sla;

    /**
     * @ORM\Column(type="utcdatetime", nullable=false)
     *
     * @var DateTime
     */
    protected DateTimeInterface $modified;

    /** @ORM\Column(type="string", name="name", nullable=false) */
    protected string $name;

    /** @ORM\Column(name="type_id", type="integer", nullable=false) */
    protected int $typeId;

    /** @ORM\OneToMany(targetEntity="Domain", mappedBy="organisation", orphanRemoval=true, cascade={"persist", "remove"}) */
    protected array|Collection $domains;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->id      = null;
        $this->domains = new ArrayCollection();

        $this->isActive = self::STATE_ACTIVE;
        $this->typeId   = self::TYPE_CLIENT;

        $this->uuid     = Uuid::uuid4();
        $now            = new DateTime('UTC', new DateTimeZone('UTC'));
        $this->created  = $now;
        $this->modified = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Organisation
    {
        $this->id = $id;
        return $this;
    }

    public function setUuid(UuidInterface $uuid): Organisation
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): Organisation
    {
        if (null === $this->id) {
            $this->created = Carbon::now('UTC');
        } else {
            $this->created = $created;
        }

        return $this;
    }

    public function getIsActive(): ?int
    {
        return $this->isActive;
    }

    public function setIsActive(int $isActive): Organisation
    {
        if (! $this->isActive) {
            $this->isActive = 1;
        } else {
            $this->isActive = $isActive;
        }

        return $this;
    }

    public function getModified(): ?DateTime
    {
        return $this->modified;
    }

    public function setModified(?DateTime $modified = null): Organisation
    {
        if (null === $modified) {
            $this->modified = Carbon::now('UTC');
        } else {
            $this->modified = $modified;
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Organisation
    {
        $validator = new OrganisationNameValidator();

        if (! $validator->isValid($name)) {
            throw OrganisationNameException::whenCreating($name);
        }
        $this->name = $name;
        return $this;
    }

    public function setTypeId(int $typeId): Organisation
    {
        $this->typeId = $typeId;
        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain)
    {
        if (! $this->hasDomain($domain)) {
            $this->domains[] = $domain;
        }
    }

    public function hasDomain(Domain $domain): bool
    {
        return $this->getDomains()->contains($domain);
    }

    public function removeDomain(Domain $domain)
    {
        $this->domains->removeElement($domain);
    }

    public function getSla(): ?Sla
    {
        return $this->sla;
    }

    public function setSla(Sla $sla): Organisation
    {
        $this->sla = $sla;
        return $this;
    }

    public function hasSla(): bool
    {
        if ($this->sla !== false) {
            return true;
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
