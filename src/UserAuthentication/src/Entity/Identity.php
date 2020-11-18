<?php
declare(strict_types=1);

namespace UserAuthentication\Entity;

use function explode;
use function strcasecmp;

class Identity
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    private $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Identity
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Identity
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Identity
    {
        $this->name = $name;
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function setRoles(string $roles)
    {
        $this->roles = explode(',', $roles);
        return $this;
    }

    public function hasRole(string $search)
    {
        foreach ($this->roles as $role) {
            if (strcasecmp($role, $search) == 0) {
                return true;
            }
        }
        return false;
    }
}
