<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use function get_object_vars;

/**
 * @ORM\Entity(repositoryClass="Ticket\Repository\QueueRepository")
 * @ORM\Table(name="queue")
 */
class Queue
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string")
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="host", type="string", nullable=true)
     *
     * @var string
     */
    private $host;

    /**
     * @ORM\Column(name="user", type="string", nullable=true)
     *
     * @var string
     */
    private $user;

    /**
     * @ORM\Column(name="password", type="string", nullable=true)
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(name="use_ssl", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $use_ssl;

    /**
     * @ORM\Column(name="fetch_from_mail", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $fetch_from_mail;

    /**
     * @ORM\OneToMany(targetEntity="Agent", mappedBy="queue", cascade={"persist"})
     *
     * @var ArrayCollection
     */

    protected $members;

    public function construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Queue
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Queue
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Queue
    {
        $this->email = $email;
        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): Queue
    {
        $this->host = $host;
        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): Queue
    {
        $this->user = $user;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Queue
    {
        $this->password = $password;
        return $this;
    }

    public function getUseSsl(): ?bool
    {
        return $this->use_ssl;
    }

    public function setUseSsl(bool $use_ssl): Queue
    {
        $this->use_ssl = $use_ssl;
        return $this;
    }

    public function getFetchFromMail(): ?bool
    {
        return $this->fetch_from_mail;
    }

    public function setFetchFromMail(bool $fetch_from_email): Queue
    {
        $this->fetch_from_mail = $fetch_from_email;
        return $this;
    }

    public function addMember(Agent $agent): Queue
    {
        if ($this->members->contains($agent)) {
            return $this;
        }

        $this->members[$agent->getId()] = $agent;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers(): ArrayCollection
    {
        return $this->members;
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
        $this->id              = $data['id'] ?? null;
        $this->name            = $data['name'] ?? null;
        $this->email           = $data['email'] ?? null;
        $this->password        = $data['password'] ?? null;
        $this->host            = $data['host'] ?? null;
        $this->user            = $data['user'] ?? null;
        $this->use_ssl         = $data['use_ssl'] ?? null;
        $this->fetch_from_mail = $data['fetch_from_mail'] ?? null;
        return $this;
    }
}
