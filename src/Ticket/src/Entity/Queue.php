<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ticket\Repository\QueueRepository;

use function array_key_exists;
use function get_object_vars;

#[ORM\Entity(repositoryClass: QueueRepository::class)]
#[ORM\Table(name: 'queue')]
class Queue
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string')]
    private string $name;

    #[ORM\Column(name: 'email', type: 'string')]
    private string $email;

    #[ORM\Column(name: 'host', type: 'string', nullable: true)]
    private ?string $host;

    #[ORM\Column(name: 'user', type: 'string', nullable: true)]
    private ?string $user;

    #[ORM\Column(name: 'password', type: 'string', nullable: true)]
    private ?string $password;

    #[ORM\Column(name: 'use_ssl', type: 'boolean', nullable: true)]
    private ?bool $useSsl;

    #[ORM\Column(name: 'fetch_from_mail', type: 'boolean', nullable: true)]
    private ?bool $fetchFromMail;

    #[ORM\ManyToMany(targetEntity: Agent::class, inversedBy: 'queues')]
    #[ORM\JoinTable(
        name: 'queue_member',
        joinColumns: [new ORM\JoinColumn(name: 'queue_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    )]
    protected Collection $members;

    public function __construct()
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
        if (array_key_exists('id', $data) && $data['id'] !== null && $data['id'] !== '') {
            $this->id = (int) $data['id'];
        }

        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $this->name = (string) $data['name'];
        }

        if (array_key_exists('email', $data) && $data['email'] !== null) {
            $this->email = (string) $data['email'];
        }

        if (array_key_exists('password', $data)) {
            $this->password = $data['password'] !== null ? (string) $data['password'] : null;
        }

        if (array_key_exists('host', $data)) {
            $this->host = $data['host'] !== null ? (string) $data['host'] : null;
        }

        if (array_key_exists('user', $data)) {
            $this->user = $data['user'] !== null ? (string) $data['user'] : null;
        }

        if (array_key_exists('use_ssl', $data)) {
            $this->useSsl = $data['use_ssl'] !== null ? (bool) $data['use_ssl'] : null;
        }

        if (array_key_exists('fetch_from_mail', $data)) {
            $this->fetchFromMail = $data['fetch_from_mail'] !== null ? (bool) $data['fetch_from_mail'] : null;
        }

        return $this;
    }
}
