<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class Agent
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="email")
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="full_name")
     *
     * @var string
     */
    private $fullName;

    /**
     * @ORM\ManyToMany (targetEntity="Queue", mappedBy="queues")
     *
     * @var ArrayCollection
     */
    protected $queues;

    public function __construct()
    {
        $this->queues = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Agent
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Agent
    {
        $this->email = $email;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): Agent
    {
        $this->fullName = $fullName;
        return $this;
    }
}
