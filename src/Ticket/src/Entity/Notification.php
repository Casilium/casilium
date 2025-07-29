<?php

declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ticket_notification')]
class Notification
{
    public const NOTIFICATION_DUE = 1;

    public const NOTIFICATION_OVERDUE = 2;

    public const SLA_BREACH = 3;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\Column(name: 'notification_type', type: 'integer')]
    protected int $notificationType;

    #[ORM\Column(name: 'notification_date', type: 'string')]
    protected string $notificationDate;

    #[ORM\OneToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'ticket_id', referencedColumnName: 'id', nullable: false)]
    protected Ticket $ticket;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Notification
    {
        $this->id = $id;
        return $this;
    }

    public function getNotificationType(): int
    {
        return $this->notificationType;
    }

    public function setNotificationType(int $notificationType): Notification
    {
        $this->notificationType = $notificationType;
        return $this;
    }

    public function getNotificationDate(): string
    {
        return $this->notificationDate;
    }

    public function setNotificationDate(string $notificationDate): Notification
    {
        $this->notificationDate = $notificationDate;
        return $this;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): Notification
    {
        $this->ticket = $ticket;
        return $this;
    }
}
