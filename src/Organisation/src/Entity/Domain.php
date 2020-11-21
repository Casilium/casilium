<?php
declare(strict_types=1);

namespace Organisation\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Laminas\Filter;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;
use Organisation\Exception\OrganisationNameException;
use Organisation\Validator\OrganisationNameValidator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function is_string;

/**
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
 *
 * @ORM\Entity(repositoryClass="Organisation\Repository\DomainRepository")
 * @ORM\Table(name="organisation_domain")
 */
class Domain
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="domains", cascade={"persist"})
     *
     * @var Organisation
     */
    protected $organisation;

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
    public function setId(int $id): Domain
    {
        $this->id = $id;
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
    public function setName(string $name): Domain
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     * @return Domain
     */
    public function setOrganisation(Organisation $organisation): Domain
    {
        $this->organisation = $organisation;
        return $this;
    }
}

