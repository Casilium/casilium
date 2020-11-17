<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;

/**
 * Class Queue
 * @package Ticket\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="queue_member")
 */

class QueueMember
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Ticket\Entity\Queue")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id", nullable=true)
     * @var Queue
     */
    private $queue;

    /**
     * @ORM\OneToOne(targetEntity="User\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * @var User
     */
    private $member;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return QueueMember
     */
    public function setId(int $id): QueueMember
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @param Queue $queue
     * @return QueueMember
     */
    public function setQueue(Queue $queue): QueueMember
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return User
     */
    public function getMember(): User
    {
        return $this->member;
    }

    /**
     * @param User $member
     * @return QueueMember
     */
    public function setMember(User $member): QueueMember
    {
        $this->member = $member;
        return $this;
    }
}