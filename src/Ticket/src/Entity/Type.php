<?php
declare(strict_types=1);

namespace Ticket\Entity;

use Doctrine\Orm\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_type")
 */
class Type
{
    public const TYPE_REQUEST  = 1;
    public const TYPE_INCIDENT = 2;
    public const TYPE_PROBLEM  = 3;

    public const TYPE_TEXT = [
        1 => 'Service Request',
        2 => 'Incident',
        3 => 'Problem',
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

    public function setId(int $id): Type
    {
        $this->id = $id;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Type
    {
        $this->description = $description;
        return $this;
    }

    public static function getStatusTextFromId(int $code): string
    {
        return self::TYPE_TEXT[$code];
    }

    public function __toString(): string
    {
        return self::getStatusTextFromId($this->id);
    }
}
