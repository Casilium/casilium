<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Ticket
 * @package Ticket\Entity
 *
 * @ORM\Entity(repositoryClass="Ticket\Repository\QueueRepository")
 * @ORM\Table(name="queue")
 */
class Queue
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string")
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="host", type="string", nullable=true)
     * @var string
     */
    private $host;

    /**
     * @ORM\Column(name="user", type="string", nullable=true)
     * @var string
     */
    private $user;

    /**
     * @ORM\Column(name="password", type="string", nullable=true)
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(name="use_ssl", type="boolean", nullable=true)
     * @var bool
     */
    private $use_ssl;

    /**
     * @ORM\Column(name="fetch_from_mail", type="boolean", nullable=true)
     * @var bool
     */
    private $fetch_from_mail;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Queue
     */
    public function setId(int $id): Queue
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
     * @return Queue
     */
    public function setName(string $name): Queue
    {
        $this->name = $name;
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
     * @return Queue
     */
    public function setEmail(string $email): Queue
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Queue
     */
    public function setHost(string $host): Queue
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return Queue
     */
    public function setUser(string $user): Queue
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Queue
     */
    public function setPassword(string $password): Queue
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseSsl(): ?bool
    {
        return $this->use_ssl;
    }

    /**
     * @param bool $use_ssl
     * @return Queue
     */
    public function setUseSsl(bool $use_ssl): Queue
    {
        $this->use_ssl = $use_ssl;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFetchFromMail(): ?bool
    {
        return $this->fetch_from_mail;
    }

    /**
     * @param bool $fetch_from_email
     * @return Queue
     */
    public function setFetchFromMail(bool $fetch_from_email): Queue
    {
        $this->fetch_from_mail = $fetch_from_email;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $data
     * @return Queue
     */
    public function exchangeArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->host = $data['host'] ?? null;
        $this->user = $data['user'] ?? null;
        $this->use_ssl = $data['ssl'] ?? null;
        $this->fetch_from_mail = $data['fetch_from_mail'] ?? null;
        return $this;
    }
}
