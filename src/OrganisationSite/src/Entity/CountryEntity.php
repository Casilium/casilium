<?php

declare(strict_types=1);

namespace OrganisationSite\Entity;

use Doctrine\ORM\Mapping as ORM;
use OrganisationSite\Repository\CountryRepository;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'country')]
class CountryEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 64)]
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): CountryEntity
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CountryEntity
    {
        $this->name = $name;
        return $this;
    }
}
