<?php

declare(strict_types=1);

namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use function count;

#[ORM\Entity(repositoryClass: 'User\Repository\UserRepository')]
#[ORM\Table(name: 'user')]
class User
{
    public const STATUS_INACTIVE = 0; // Inactive user
    public const STATUS_ACTIVE   = 1; // Active user
    public const STATUS_RETIRED  = 2; // Retired user

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'email')]
    private string $email;

    #[ORM\Column(name: 'full_name')]
    private string $fullName;

    #[ORM\Column(name: 'password')]
    private string $password;

    #[ORM\Column(name: 'status', type: 'integer')]
    private int $status;

    #[ORM\Column(name: 'date_created')]
    private string $dateCreated;

    #[ORM\Column(name: 'pwd_reset_token')]
    private string $passwordResetToken;

    #[ORM\Column(name: 'pwd_reset_token_creation_date')]
    private string $passwordResetTokenCreationDate;

    #[ORM\Column(name: 'secret_key')]
    private ?string $secretKey = '';

    #[ORM\Column(name: 'mfa_enabled', type: 'boolean')]
    private bool $mfaEnabled;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles      = new ArrayCollection();
        $this->mfaEnabled = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public static function getStatusList(): array
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_RETIRED  => 'Retired',
        ];
    }

    public function getStatusAsString(): string
    {
        $list = self::getStatusList();
        return $list[$this->status] ?? 'Unknown';
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    public function setDateCreated(string $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }

    public function setMfaEnabled(bool $mfaEnabled): User
    {
        $this->mfaEnabled = $mfaEnabled;
        return $this;
    }

    public function getPasswordResetToken(): string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(string $passwordResetToken): void
    {
        $this->passwordResetToken = $passwordResetToken;
    }

    public function getPasswordResetTokenCreationDate(): string
    {
        return $this->passwordResetTokenCreationDate;
    }

    public function setPasswordResetTokenCreationDate(string $passwordResetTokenCreationDate): void
    {
        $this->passwordResetTokenCreationDate = $passwordResetTokenCreationDate;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): User
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function getRolesAsString(): string
    {
        $roleList = '';

        $count = count($this->roles);
        $i     = 0;
        foreach ($this->roles as $role) {
            $roleList .= $role->getName();
            if ($i < $count - 1) {
                $roleList .= ',';
            }
            $i++;
        }
        return $roleList;
    }

    public function addRole(Role $role): User
    {
        $this->roles->add($role);
        return $this;
    }
}
