<?php
declare(strict_types=1);

namespace Organisation\Entity;

use DateTime;
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
    public const STATE_ACTIVE = 1;
    public const STATE_DISABLED = 2;

    public const TYPE_CLIENT = 1;
    public const TYPE_SUPPLIER = 2;
    public const TYPE_BOTH = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     *
     * @var Uuid
     */
    protected $uuid;

    /**
     * @ORM\Column(type="utcdatetime", nullable=false)
     *
     * @var DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $is_active;

    /**
     * @ORM\OneToOne(targetEntity="ServiceLevel\Entity\Sla")
     * @ORM\JoinColumn(name="sla_id", referencedColumnName="id", nullable=true)
     *
     * @var Sla|null
     */
    protected $sla;

    /**
     * @ORM\Column(type="utcdatetime", nullable=false)
     *
     * @var DateTime
     */
    protected $modified;

    /**
     * @ORM\Column(type="string", name="name", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $type_id;

    /**
     * @ORM\OneToMany(targetEntity="Domain", mappedBy="organisation", orphanRemoval=true, cascade={"persist", "remove"})
     *
     * @var Domain[]
     */
    protected $domains;

    /**
     * Organisation constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->domains = new ArrayCollection();

        $this->is_active = self::STATE_ACTIVE;
        $this->type_id = self::TYPE_CLIENT;

        $this->uuid = Uuid::uuid4();
        $now = new \DateTime('UTC', new \DateTimeZone('UTC'));
        $this->created = $now;
        $this->modified = $now;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): Organisation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set UUID value
     */
    public function setUuid(UuidInterface $uuid): Organisation
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @throws Exception
     */
    public function setCreated(DateTime $created): Organisation
    {
        if (null === $this->created && null === $this->id) {
            $this->created = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $this->created = $created;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    /**
     * @param int $is_active
     * @return $this
     */
    public function setIsActive(int $is_active): Organisation
    {
        if (null === $this->is_active) {
            $this->is_active = 1;
        } else {
            $this->is_active = (int) $is_active;
        }

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getModified(): ?DateTime
    {
        return $this->modified;
    }

    /**
     * @throws Exception
     */
    public function setModified(?DateTime $modified = null): Organisation
    {
        if (null === $modified) {
            $this->modified = new DateTime("now", new DateTimeZone('UTC'));
        } else {
            $this->modified = $modified;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Organisation
    {
        $validator = new OrganisationNameValidator();

        if (! $validator->isValid($name)) {
            throw OrganisationNameException::whenCreating($name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Set organisation type
     *
     * @return $this
     */
    public function setTypeId(int $type_id): Organisation
    {
        $this->type_id = $type_id;
        return $this;
    }

    /**
     * Get organisation type
     *
     * @return int|null
     */
    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    /**
     * Get domain name
     *
     * @return ArrayCollection
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    /**
     * Add domain to organisation
     *
     * @param Domain $domain
     */
    public function addDomain(Domain $domain)
    {
        $this->domains[] = $domain;
    }

    /**
     * Check if organisation has domain
     *
     * @param Domain $domain
     * @return bool
     */
    public function hasDomain(Domain $domain)
    {
        return $this->getDomains()->contains($domain);
    }

    /**
     * Remove domain
     *
     * @param Domain $domain
     */
    public function removeDomain(Domain $domain)
    {
        $this->domains->removeElement($domain);
    }

    /**
     * @return Sla
     */
    public function getSla(): ?Sla
    {
        return $this->sla;
    }

    /**
     * @param Sla $sla
     * @return Organisation
     */
    public function setSla(Sla $sla): Organisation
    {
        $this->sla = $sla;
        return $this;
    }
}
