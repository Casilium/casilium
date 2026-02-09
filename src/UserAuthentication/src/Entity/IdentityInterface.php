<?php

declare(strict_types=1);

namespace UserAuthentication\Entity;

interface IdentityInterface
{
    public function getId(): ?int;

    public function setId(int $id): IdentityInterface;

    public function getEmail(): ?string;

    public function setEmail(string $email): IdentityInterface;

    public function getName(): ?string;

    public function setName(string $name): IdentityInterface;

    public function setRoles(string $roles): IdentityInterface;

    public function hasRole(string $search): bool;
}
