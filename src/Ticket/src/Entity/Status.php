<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Orm\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_status")
 */
class Status
{
    public const STATUS_OPEN        = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_ON_HOLD     = 3;
    public const STATUS_RESOLVED    = 4;
    public const STATUS_CLOSED      = 5;

    private const STATUS_TEXT = [
        self::STATUS_OPEN        => 'Open',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_ON_HOLD     => 'On Hold',
        self::STATUS_RESOLVED    => 'Resolved',
        self::STATUS_CLOSED      => 'Closed',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="description")
     *
     * @var string
     */
    private $description;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Status
    {
        $this->id = $id;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Status
    {
        $this->description = $description;
        return $this;
    }

    public static function getStatusTextFromId(int $code): string
    {
        return self::STATUS_TEXT[$code];
    }
}
