<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Orm\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_priority")
 */
class Priority
{
    public const PRIORITY_CRITICAL = 2;
    public const PRIORITY_URGENT   = 3;
    public const PRIORITY_HIGH     = 4;
    public const PRIORITY_MEDIUM   = 5;
    public const PRIORITY_LOW      = 6;

    private const PRIORITY = [
        self::PRIORITY_CRITICAL => 'Critical',
        self::PRIORITY_URGENT   => 'Urgent',
        self::PRIORITY_HIGH     => 'High',
        self::PRIORITY_MEDIUM   => 'Medium',
        self::PRIORITY_LOW      => 'Low',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name")
     *
     * @var string
     */
    private $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Priority
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Priority
    {
        $this->name = $name;
        return $this;
    }

    public static function getPriorityDescription(int $code): string
    {
        return self::PRIORITY[$code];
    }
}
