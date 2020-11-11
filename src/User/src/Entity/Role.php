<?php
declare(strict_types=1);

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Role
 *
 * @ORM\Entity()
 * @ORM\Table(name="role")
 */
class Role
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="description")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="date_created")
     * @var string
     */
    private $dateCreated;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role", inversedBy="childRoles")
     * @ORM\JoinTable(name="role_hierarchy",
     *     joinColumns={@ORM\JoinColumn(name="child_role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="parent_role_id", referencedColumnName="id")}
     * )
     * @var ArrayCollection
     */
    private $parentRoles;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role", mappedBy="parentRoles")
     * @ORM\JoinTable(name="role_hierarchy",
     *     joinColumns={@ORM\JoinColumn(name="parent_role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="child_role_id", referencedColumnName="id")}
     * )
     * @var ArrayCollection
     */
    private $childRoles;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Permission", inversedBy="roles")
     * @ORM\JoinTable(name="role_permission",
     *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     * @var ArrayCollection
     */
    private $permissions;

    public function __construct()
    {
        $this->parentRoles = new ArrayCollection();
        $this->childRoles  = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Role
     */
    public function setId(int $id): Role
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Role
     */
    public function setDescription(string $description): Role
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    /**
     * @param string $dateCreated
     * @return Role
     */
    public function setDateCreated(string $dateCreated): Role
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getParentRoles(): Collection
    {
        return $this->parentRoles;
    }

    /**
     * @return Collection
     */
    public function getChildRoles(): Collection
    {
        return $this->childRoles;
    }

    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addParent(Role $role): bool
    {
        if ($this->getId() == $role->getId()) {
            return false;
        }

        if (! $this->hasParent($role)) {
            $this->parentRoles->add($role);
            $role->getChildRoles()->add($this);
            return true;
        }

        return false;
    }

    public function hasParent(Role $role): bool
    {
        if ($this->getParentRoles()->contains($role)) {
            return true;
        }

        return false;
    }

    public function clearParentRoles(): Role
    {
        $this->parentRoles = new ArrayCollection();
        return $this;
    }
}
