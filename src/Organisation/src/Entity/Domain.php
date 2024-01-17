<?php

declare(strict_types=1);

namespace Organisation\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    protected int $id;

    /** @ORM\Column(type="string", name="name", nullable=false) */
    protected string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="domains")
     * @ORM\JoinColumn("organisation_id", referencedColumnName="id", onDelete="cascade")
     */
    protected Organisation $organisation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Domain
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Domain
    {
        $this->name = $name;
        return $this;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): Domain
    {
        $this->organisation = $organisation;
        return $this;
    }
}
