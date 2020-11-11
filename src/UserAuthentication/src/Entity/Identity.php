<?php
declare(strict_types=1);

namespace UserAuthentication\Entity;

use User\Entity\Role;

class Identity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    private $roles = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Identity
     */
    public function setId(int $id): Identity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Identity
     */
    public function setEmail(string $email): Identity
    {
        $this->email = $email;
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
     * @return Identity
     */
    public function setName(string $name): Identity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getId();
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
