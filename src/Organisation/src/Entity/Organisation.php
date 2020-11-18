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
 * @ORM\Entity(repositoryClass="Organisation\Repository\OrganisationRepository")
 * @ORM\Table(name="organisation")
 */
class Organisation implements InputFilterProviderInterface, OrganisationInterface
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

    public function __construct()
    {
        $this->type_id = 1;
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

    /**
     * Set UUID value
     */
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

    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    public function setIsActive(int $is_active): Organisation
    {
        if (null === $this->is_active) {
            $this->is_active = 1;
        } else {
            $this->is_active = (int) $is_active;
        }

        return $this;
    }

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

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    /**
     * Build array from object vars
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return [
            'id'        => $this->getId(),
            'uuid'      => $this->getUuid(),
            'created'   => $this->getCreated()->format('Y-m-d H:i:s'),
            'is_active' => $this->getIsActive(),
            'modified'  => $this->getModified()->format('Y-m-d H:i:s'),
            'name'      => $this->getName(),
            'type_id'   => $this->type_id,
        ];
    }

    /**
     * Exchange array values (alias for setValues)
     *
     * @param array $data
     * @throws Exception
     */
    public function exchangeArray(array $data): Organisation
    {
        return $this->setValues($data);
    }

    /**
     * Populate object vars from array
     *
     * @param array $data
     * @throws Exception
     */
    public function setValues(array $data): Organisation
    {
        $this->id = isset($data['id']) ? (int) $data['id'] : null;

        if (! isset($this->uuid)) {
            $this->uuid = Uuid::uuid4();
        } elseif (isset($data['uuid']) && is_string($data['uuid']) && Uuid::isValid($data['uuid'])) {
            $this->setUuid(Uuid::fromString($data['uuid']));
        }

        if (! isset($this->created)) {
            $this->setCreated(new DateTime('now'));
        }

        if (isset($data['is_active'])) {
            $this->setIsActive($data['is_active']);
        } else {
            $this->setIsActive(1);
        }

        $this->setModified(new DateTime('now'));

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['type_id'])) {
            $this->type_id = (int) $data['type_id'];
        }

        return $this;
    }

    /**
     * Input filter and validation
     */
    public function getInputFilterSpecification(): InputFilterInterface
    {
        $factory = new InputFilterFactory();

        return $factory->createInputFilter([
            'id'        => [
                'required'   => false,
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
            'uuid'      => [
                'required'   => false,
                'validators' => [
                    ['name' => Validator\Uuid::class],
                ],
            ],
            'name'      => [
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'is_active' => [
                'required'   => false,
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
        ]);
    }
}
