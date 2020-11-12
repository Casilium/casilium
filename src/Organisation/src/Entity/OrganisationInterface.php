<?php

namespace Organisation\Entity;


use Laminas\InputFilter\InputFilterInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
 *
 * @ORM\Entity(repositoryClass="Organisation\Repository\OrganisationRepository")
 * @ORM\Table(name="organisation")
 */
interface OrganisationInterface
{
    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @param int $id
     * @return Organisation
     */
    public function setId(int $id): Organisation;

    /**
     * Set UUID value
     * @param UuidInterface $uuid
     * @return Organisation
     */
    public function setUuid(UuidInterface $uuid): Organisation;

    public function getUuid(): UuidInterface;

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime;

    /**
     * @param \DateTime $created
     * @return Organisation
     * @throws \Exception
     */
    public function setCreated(\DateTime $created): Organisation;

    /**
     * @return int
     */
    public function getIsActive(): ?int;

    /**
     * @param int $is_active
     * @return Organisation
     */
    public function setIsActive(int $is_active): Organisation;

    /**
     * @return \DateTime
     */
    public function getModified(): ?\DateTime;

    /**
     * @param \DateTime|null $modified
     * @return Organisation
     * @throws \Exception
     */
    public function setModified(\DateTime $modified = null): Organisation;

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * @return Organisation
     */
    public function setName(string $name): Organisation;

    /**
     * Set organisation type
     *
     * @param int $type_id
     * @return $this
     */
    public function setTypeId(int $type_id): Organisation;

    /**
     * @return int|null
     */
    public function getTypeId(): ?int;

    /**
     * Build array from object vars
     * @return array
     */
    public function getArrayCopy(): array;

    /**
     * Populate object vars from array
     * @param array $data
     * @return Organisation
     * @throws \Exception
     */
    public function setValues(array $data): Organisation;

    /**
     * Input filter and validation
     *
     * @return InputFilterInterface
     */
    public function getInputFilterSpecification(): InputFilterInterface;
}