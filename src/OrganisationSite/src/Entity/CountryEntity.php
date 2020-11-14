<?php
declare(strict_types=1);

namespace OrganisationSite\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\OrganisationSite\Repository\CountryRepository")
 * @ORM\Table(name="country")
 */
class CountryEntity
{
    /**
     * Internal Country ID
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * Country name
     * @ORM\Column(name="name", type="string", length=64)
     * @var string
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CountryEntity
     */
    public function setId(int $id): CountryEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CountryEntity
     */
    public function setName(string $name): CountryEntity
    {
        $this->name = $name;
        return $this;
    }
}
