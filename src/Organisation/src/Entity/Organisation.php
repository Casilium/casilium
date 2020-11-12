<?php
declare(strict_types=1);

namespace Organisation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Organisation\Exception\OrganisationNameException;
use Organisation\Validator\OrganisationNameValidator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\Filter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

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
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @var uuid
     */
    protected $uuid;

    /**
     * @ORM\Column(type="utcdatetime", nullable=false)
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected $is_active;

    /**
     * @ORM\Column(type="utcdatetime", nullable=false)
     * @var \DateTime
     */
    protected $modified;

    /**
     * @ORM\Column(type="string", name="name", nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected $type_id;

    public function __construct() {
        $this->type_id = 1;
    }

    /**
     * @return int
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Organisation
     */
    public function setId(int $id) : Organisation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set UUID value
     * @param UuidInterface $uuid
     * @return Organisation
     */
    public function setUuid(UuidInterface $uuid) : Organisation
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return Organisation
     * @throws \Exception
     */
    public function setCreated(\DateTime $created) : Organisation
    {
        if (null === $this->created && null === $this->id) {
            $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
        } else {
            $this->created = $created;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    /**
     * @param int $is_active
     * @return Organisation
     */
    public function setIsActive(int $is_active): Organisation
    {
        if (null === $this->is_active) {
            $this->is_active = 1;
        } else {
            $this->is_active = (int)$is_active;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified(): ?\DateTime
    {
        return $this->modified;
    }

    /**
     * @param \DateTime|null $modified
     * @return Organisation
     * @throws \Exception
     */
    public function setModified(\DateTime $modified = null): Organisation
    {
        if (null === $modified) {
            $this->modified = new \DateTime("now",new \DateTimeZone('UTC'));
        } else {
            $this->modified = $modified;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Organisation
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
     * @param int $type_id
     * @return $this
     */
    public function setTypeId(int $type_id) : Organisation
    {
        $this->type_id = $type_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTypeId() : ?int
    {
        return $this->type_id;
    }

    /**
     * Build array from object vars
     * @return array
     */
    public function getArrayCopy() : array
    {
        return [
            'id' => $this->getId(),
            'uuid' => $this->getUuid(),
            'created' => $this->getCreated()->format('Y-m-d H:i:s'),
            'is_active' => $this->getIsActive(),
            'modified' => $this->getModified()->format('Y-m-d H:i:s'),
            'name' => $this->getName(),
            'type_id' => $this->type_id,
        ];
    }

    /**
     * Exchange array values (alias for setValues)
     *
     * @param array $data
     * @return Organisation
     * @throws \Exception
     */
    public function exchangeArray(array $data): Organisation
    {
        return $this->setValues($data);
    }

    /**
     * Populate object vars from array
     * @param array $data
     * @return Organisation
     * @throws \Exception
     */
    public function setValues(array $data) : Organisation
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;

        if (! isset($this->uuid)) {
            $this->uuid = Uuid::uuid4();
        } elseif (isset($data['uuid']) && is_string($data['uuid']) && Uuid::isValid($data['uuid'])) {
            $this->setUuid(Uuid::fromString($data['uuid']));
        }

        if (! isset($this->created)) {
            $this->setCreated(new \DateTime('now'));
        }

        if (isset($data['is_active'])) {
            $this->setIsActive($data['is_active']);
        } else {
            $this->setIsActive(1);
        }

        $this->setModified(new \DateTime('now'));

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['type_id'])) {
            $this->type_id = (int)$data['type_id'];
        }

        return $this;
    }

    /**
     * Input filter and validation
     *
     * @return InputFilterInterface
     */
    public function getInputFilterSpecification() : InputFilterInterface
    {
        $factory = new InputFilterFactory();

        return $factory->createInputFilter([
            'id' => [
                'required' => false,
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
            'uuid' => [
                'required' => false,
                'validators' => [
                    ['name' => Validator\Uuid::class],
                ],
            ],
            'name' => [
                'required' => true,
                'filters' => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                ],
            ],
            'is_active' => [
                'required' => false,
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ],
        ]);
    }
}
