<?php

declare(strict_types=1);

namespace UserAuthentication\Entity;

interface IdentityInterface
{
    public function getId(): ?int;

    public function setId(int $id): Identity;

    public function getEmail(): ?string;

    public function setEmail(string $email): Identity;

    public function getName(): ?string;

    public function setName(string $name): Identity;

    public function setRoles(string $roles): Identity;

    public function hasRole(string $search): bool;
}
