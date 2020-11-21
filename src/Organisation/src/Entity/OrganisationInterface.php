<?php

namespace Organisation\Entity;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
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
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     * @return Organisation
     */
    public function setId(int $id): Organisation;

    /**
     * Set UUID value
     */
    public function setUuid(UuidInterface $uuid): Organisation;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime;

    /**
     * @throws Exception
     */
    public function setCreated(DateTime $created): Organisation;

    /**
     * @return int|null
     */
    public function getIsActive(): ?int;

    /**
     * @param int $is_active
     * @return Organisation
     */
    public function setIsActive(int $is_active): Organisation;

    /**
     * @return DateTime|null
     */
    public function getModified(): ?DateTime;

    /**
     * @throws Exception
     */
    public function setModified(?DateTime $modified = null): Organisation;

    /**
     * @return string|null
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
     * @return Organisation
     */
    public function setTypeId(int $type_id): Organisation;

    /**
     * Get organisation type
     *
     * @return int|null
     */
    public function getTypeId(): ?int;

    /**
     * Get domain name
     *
     * @return ArrayCollection
     */
    public function getDomains(): Collection;

    /**
     * Add domain to organisation
     *
     * @param Domain $domain
     */
    public function addDomain(Domain $domain);

    /**
     * Check if organisation has domain
     *
     * @param Domain $domain
     * @return bool
     */
    public function hasDomain(Domain $domain);

    /**
     * Remove domain
     *
     * @param Domain $domain
     */
    public function removeDomain(Domain $domain);
}