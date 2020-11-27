<?php

declare(strict_types=1);

namespace ServiceLevel\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ticket\Entity\Priority;

/**
 * @ORM\Entity
 * @ORM\Table(name="sla_target")
 */
class SlaTarget
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
     * @ORM\ManyToOne(targetEntity="Sla", inversedBy="target_sla")
     * @ORM\JoinColumn(name="sla_id", referencedColumnName="id")
     * @var Sla
     */
    protected $sla;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Priority")
     * @ORM\JoinColumn(name="priority_id", referencedColumnName="id", nullable=false)
     *
     * @var Priority
     */
    protected $priority;

    /**
     * @ORM\Column(name="response_time", type="integer")
     * @var int
     */
    protected $responseTime;

    /**
     * @ORM\Column(name="resolve_time", type="integer")
     * @var int
     */
    protected $resolveTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): SlaTarget
    {
        $this->id = $id;
        return $this;
    }

    public function setSla(Sla $sla): SlaTarget
    {
        $this->sla = $sla;
        return $this;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): SlaTarget
    {
        $this->priority = $priority;
        return $this;
    }

    public function getResponseTime(): int
    {
        return $this->responseTime;
    }

    public function setResponseTime(int $responseTime): SlaTarget
    {
        $this->responseTime = $responseTime;
        return $this;
    }

    public function getResolveTime(): int
    {
        return $this->resolveTime;
    }

    public function setResolveTime(int $resolveTime): SlaTarget
    {
        $this->resolveTime = $resolveTime;
        return $this;
    }

    public function exchangeArray(array $data)
    {
        $this->id           = $data['id'] ?? null;
        $this->responseTime = (int) $data['response_time'];
        $this->resolveTime  = (int) $data['resolve_time'];
        $this->priority     = $data['priority'];
        $this->sla          = $data['sla'];
    }

    public function getArrayCopy(): array
    {
        return [
            'id'            => (int) $this->id,
            'response_time' => (int) $this->responseTime,
            'resolve_time'  => (int) $this->resolveTime,
            'priority'      => (int) $this->getPriority()->getId(),
            'sla'           => (int) $this->sla->getId(),
        ];
    }
}
