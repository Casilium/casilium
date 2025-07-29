<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'queue_member')]

class QueueMember
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Queue::class)]
    #[ORM\JoinColumn(name: 'queue_id', referencedColumnName: 'id', nullable: true)]
    private ?Queue $queue;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $member;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): QueueMember
    {
        $this->id = $id;
        return $this;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function setQueue(Queue $queue): QueueMember
    {
        $this->queue = $queue;
        return $this;
    }

    public function getMember(): User
    {
        return $this->member;
    }

    public function setMember(User $member): QueueMember
    {
        $this->member = $member;
        return $this;
    }
}
