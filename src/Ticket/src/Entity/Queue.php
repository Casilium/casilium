<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $useSsl;

    /**
     * @ORM\Column(name="fetch_from_mail", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $fetchFromMail;

    /**
     * @ORM\ManyToMany (targetEntity="Agent", inversedBy="queues")
     * @ORM\JoinTable (name="queue_member",
     *     joinColumns={@ORM\JoinColumn(name="queue_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn("user_id", referencedColumnName="id")}
     * )
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
        return $this->useSsl;
    }

    public function setUseSsl(bool $useSsl): Queue
    {
        $this->useSsl = $useSsl;
        return $this;
    }

    public function getFetchFromMail(): ?bool
    {
        return $this->fetchFromMail;
    }

    public function setFetchFromMail(bool $fetchFromMail): Queue
    {
        $this->fetchFromMail = $fetchFromMail;
        return $this;
    }

    public function addMember(Agent $agent): Queue
    {
        $this->members->add($agent);
        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function removeMember(Agent $agent): Queue
    {
        $this->members->removeElement($agent);
        return $this;
    }

    public function hasMember(Agent $agent): bool
    {
        return $this->members->contains($agent);
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
        $this->id            = $data['id'] ?? null;
        $this->name          = $data['name'] ?? null;
        $this->email         = $data['email'] ?? null;
        $this->password      = $data['password'] ?? null;
        $this->host          = $data['host'] ?? null;
        $this->user          = $data['user'] ?? null;
        $this->useSsl        = $data['use_ssl'] ?? null;
        $this->fetchFromMail = $data['fetch_from_mail'] ?? null;
        return $this;
    }
}
