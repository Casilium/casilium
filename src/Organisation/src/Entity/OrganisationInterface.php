<?php

namespace Organisation\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
    public function getId(): ?int;

    public function setId(int $id): Organisation;

    public function setUuid(UuidInterface $uuid): Organisation;

    public function getUuid(): UuidInterface;

    public function getCreated(): DateTime;

    /**
     * @throws Exception
     */
    public function setCreated(DateTime $created): Organisation;

    public function getIsActive(): ?int;

    public function setIsActive(int $isActive): Organisation;

    public function getModified(): ?DateTime;

    public function setModified(?DateTime $modified = null): Organisation;

    public function getName(): ?string;

    public function setName(string $name): Organisation;

    public function setTypeId(int $typeId): Organisation;

    public function getTypeId(): ?int;

    /**
     * Get domain name
     *
     * @return ArrayCollection
     */
    public function getDomains(): Collection;

    public function addDomain(Domain $domain);

    public function hasDomain(Domain $domain);

    public function removeDomain(Domain $domain);
}
