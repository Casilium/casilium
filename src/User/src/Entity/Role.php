<?php

declare(strict_types=1);

namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'role')]
class Role
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'name')]
    private string $name;

    #[ORM\Column(name: 'description')]
    private string $description;

    #[ORM\Column(name: 'date_created')]
    private string $dateCreated;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'childRoles')]
    #[ORM\JoinTable(name: 'role_hierarchy')]
    #[ORM\JoinColumn(name: 'child_role_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'parent_role_id', referencedColumnName: 'id')]
    private Collection $parentRoles;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'parentRoles')]
    #[ORM\JoinTable(name: 'role_hierarchy')]
    #[ORM\JoinColumn(name: 'parent_role_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'child_role_id', referencedColumnName: 'id')]
    private Collection $childRoles;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'roles')]
    #[ORM\JoinTable(name: 'role_permission')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'permission_id', referencedColumnName: 'id')]
    private Collection $permissions;

    public function __construct()
    {
        $this->parentRoles = new ArrayCollection();
        $this->childRoles  = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Role
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Role
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Role
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    public function setDateCreated(string $dateCreated): Role
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getParentRoles(): Collection
    {
        return $this->parentRoles;
    }

    public function getChildRoles(): Collection
    {
        return $this->childRoles;
    }

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addParent(Role $role): bool
    {
        if ($this->getId() === $role->getId()) {
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
