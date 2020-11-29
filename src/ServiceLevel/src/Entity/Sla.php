<?php

declare(strict_types=1);

namespace ServiceLevel\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sla")
 */
class Sla
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     *
     * @var string Name of SLA
     */
    protected $name;

    /**
     * @ORM\OneToOne(targetEntity="BusinessHours")
     * @ORM\JoinColumn(name="business_hours_id", referencedColumnName="id", nullable=false)
     *
     * @var BusinessHours
     */
    protected $businessHours;

    /**
     * @ORM\OneToMany(targetEntity="SlaTarget", mappedBy="sla", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $slaTarget;

    public function __construct()
    {
        $this->slaTarget = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Sla
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Sla
    {
        $this->name = $name;
        return $this;
    }

    public function getBusinessHours(): BusinessHours
    {
        return $this->businessHours;
    }

    public function setBusinessHours(BusinessHours $businessHours): Sla
    {
        $this->businessHours = $businessHours;
        return $this;
    }

    public function getSlaTarget(): ?Collection
    {
        return $this->slaTarget;
    }

    public function addSlaTarget(SlaTarget $target): void
    {
        $this->slaTarget[$target->getId()] = $target;
    }

    public function removeTarget(SlaTarget $target): void
    {
        if ($this->slaTarget->contains($target)) {
            $this->slaTarget->removeElement($target);
        }
    }

    public function getArrayCopy(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'business_hours' => $this->businessHours,
            'sla_target'     => $this->slaTarget,
        ];
    }
}
