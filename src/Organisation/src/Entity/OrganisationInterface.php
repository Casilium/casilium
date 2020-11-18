<?php

namespace Organisation\Entity;

use DateTime;
use Exception;
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
    public function getId(): ?int;

    public function setId(int $id): Organisation;

    /**
     * Set UUID value
     */
    public function setUuid(UuidInterface $uuid): Organisation;

    public function getUuid(): UuidInterface;

    public function getCreated(): DateTime;

    /**
     * @throws Exception
     */
    public function setCreated(DateTime $created): Organisation;

    public function getIsActive(): ?int;

    public function setIsActive(int $is_active): Organisation;

    public function getModified(): ?DateTime;

    /**
     * @throws Exception
     */
    public function setModified(?DateTime $modified = null): Organisation;

    public function getName(): ?string;

    public function setName(string $name): Organisation;

    /**
     * Set organisation type
     *
     * @return $this
     */
    public function setTypeId(int $type_id): Organisation;

    public function getTypeId(): ?int;

    /**
     * Build array from object vars
     *
     * @return array
     */
    public function getArrayCopy(): array;

    /**
     * Populate object vars from array
     *
     * @param array $data
     * @throws Exception
     */
    public function setValues(array $data): Organisation;

    /**
     * Input filter and validation
     */
    public function getInputFilterSpecification(): InputFilterInterface;
}
