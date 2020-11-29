<?php

declare(strict_types=1);

namespace ServiceLevel\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ticket\Entity\Priority;
use function preg_match;

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
     *
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
     * @ORM\Column(name="response_time", type="string", nullable=true)
     *
     * @var string
     */
    protected $responseTime;

    /**
     * @ORM\Column(name="resolve_time", type="string", nullable=true)
     *
     * @var string
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

    public function getResponseTime(): string
    {
        return $this->responseTime;
    }

    public function setResponseTime(string $responseTime): SlaTarget
    {
        $this->responseTime = $responseTime;
        return $this;
    }

    public function getResolveTime(): string
    {
        return $this->resolveTime;
    }

    public function setResolveTime(string $resolveTime): SlaTarget
    {
        $this->resolveTime = $resolveTime;
        return $this;
    }

    public function getTimeInSeconds(string $string): int
    {
        if (! preg_match('/^(\d{2}):(\d{2})$/', $string, $matches)) {
            throw new Exception('Invalid duration');
        }

        $hours   = (int) $matches[1] * 60 * 60;
        $minutes = (int) $matches[2] * 60;
        return $hours + $minutes;
    }

    public function exchangeArray(array $data)
    {
        $this->id           = $data['id'] ?? null;
        $this->responseTime = $data['response_time'];
        $this->resolveTime  = $data['resolve_time'];
        $this->priority     = $data['priority'];
        $this->sla          = $data['sla'];
    }

    public function getArrayCopy(): array
    {
        return [
            'id'            => (int) $this->id,
            'response_time' => $this->responseTime,
            'resolve_time'  => $this->resolveTime,
            'priority'      => (int) $this->getPriority()->getId(),
            'sla'           => (int) $this->sla->getId(),
        ];
    }
}
