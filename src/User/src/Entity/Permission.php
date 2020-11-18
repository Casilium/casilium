<?php
declare(strict_types=1);

namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="permission")
 */
class Permission
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
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

    /**
     * @ORM\Column(name="description")
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="date_created")
     *
     * @var string
     */
    private $dateCreated;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role", mappedBy="permissions")
     * @ORM\JoinTable(name="role_permission",
     *     joinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Permission
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Permission
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Permission
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    public function setDateCreated(string $dateCreated): Permission
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getRoles(): ArrayCollection
    {
        return $this->roles;
    }
}
