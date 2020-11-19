<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Orm\Mapping as ORM;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Agent
     */
    public function setId(int $id): Agent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Agent
     */
    public function setEmail(string $email): Agent
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return Agent
     */
    public function setFullName(string $fullName): Agent
    {
        $this->fullName = $fullName;
        return $this;
    }
}