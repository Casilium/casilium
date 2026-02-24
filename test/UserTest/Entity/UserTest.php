<?php

declare(strict_types=1);

namespace UserTest\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use User\Entity\Role;
use User\Entity\User;
use UserAuthentication\Entity\IdentityInterface;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testConstructorInitializesDefaults(): void
    {
        $user = new User();

        $this->assertFalse($user->isMfaEnabled());
        $this->assertCount(0, $user->getRoles());
    }

    public function testSetAndGetId(): void
    {
        $this->user->setId(123);
        $this->assertEquals(123, $this->user->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getEmail());
    }

    public function testSetAndGetFullName(): void
    {
        $fullName = 'John Doe';
        $this->user->setFullName($fullName);
        $this->assertEquals($fullName, $this->user->getFullName());
    }

    public function testSetAndGetPassword(): void
    {
        $password = 'hashed_password_123';
        $this->user->setPassword($password);
        $this->assertEquals($password, $this->user->getPassword());
    }

    #[DataProvider('statusProvider')]
    public function testSetAndGetStatus(int $status): void
    {
        $this->user->setStatus($status);
        $this->assertEquals($status, $this->user->getStatus());
    }

    public static function statusProvider(): array
    {
        return [
            'inactive' => [User::STATUS_INACTIVE],
            'active'   => [User::STATUS_ACTIVE],
            'retired'  => [User::STATUS_RETIRED],
        ];
    }

    public function testGetStatusList(): void
    {
        $expected = [
            User::STATUS_INACTIVE => 'Inactive',
            User::STATUS_ACTIVE   => 'Active',
            User::STATUS_RETIRED  => 'Retired',
        ];

        $this->assertEquals($expected, User::getStatusList());
    }

    #[DataProvider('statusStringProvider')]
    public function testGetStatusAsString(int $status, string $expected): void
    {
        $this->user->setStatus($status);
        $this->assertEquals($expected, $this->user->getStatusAsString());
    }

    public static function statusStringProvider(): array
    {
        return [
            'inactive status' => [User::STATUS_INACTIVE, 'Inactive'],
            'active status'   => [User::STATUS_ACTIVE, 'Active'],
            'retired status'  => [User::STATUS_RETIRED, 'Retired'],
            'unknown status'  => [999, 'Unknown'],
        ];
    }

    public function testSetAndGetDateCreated(): void
    {
        $dateCreated = '2023-01-01 12:00:00';
        $this->user->setDateCreated($dateCreated);
        $this->assertEquals($dateCreated, $this->user->getDateCreated());
    }

    public function testSetAndGetPasswordResetToken(): void
    {
        $token = 'reset_token_123';
        $this->user->setPasswordResetToken($token);
        $this->assertEquals($token, $this->user->getPasswordResetToken());
    }

    public function testSetAndGetPasswordResetTokenCreationDate(): void
    {
        $date = '2023-01-01 12:00:00';
        $this->user->setPasswordResetTokenCreationDate($date);
        $this->assertEquals($date, $this->user->getPasswordResetTokenCreationDate());
    }

    public function testMfaEnabledDefaults(): void
    {
        $this->assertFalse($this->user->isMfaEnabled());
    }

    public function testSetMfaEnabledReturnsUserInstance(): void
    {
        $result = $this->user->setMfaEnabled(true);
        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($this->user->isMfaEnabled());
    }

    public function testSetAndGetSecretKey(): void
    {
        $secretKey = 'secret_key_123';
        $result    = $this->user->setSecretKey($secretKey);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($secretKey, $this->user->getSecretKey());
    }

    public function testAddRoleReturnsUserInstance(): void
    {
        $role   = $this->createMock(Role::class);
        $result = $this->user->addRole($role);

        $this->assertInstanceOf(User::class, $result);
        $this->assertCount(1, $this->user->getRoles());
    }

    public function testGetRolesAsStringWithNoRoles(): void
    {
        $this->assertEquals('', $this->user->getRolesAsString());
    }

    public function testGetRolesAsStringWithSingleRole(): void
    {
        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('Admin');

        $this->user->addRole($role);
        $this->assertEquals('Admin', $this->user->getRolesAsString());
    }

    public function testGetRolesAsStringWithMultipleRoles(): void
    {
        $role1 = $this->createMock(Role::class);
        $role1->method('getName')->willReturn('Admin');

        $role2 = $this->createMock(Role::class);
        $role2->method('getName')->willReturn('User');

        $this->user->addRole($role1);
        $this->user->addRole($role2);

        $this->assertEquals('Admin,User', $this->user->getRolesAsString());
    }

    public function testImplementsIdentityInterface(): void
    {
        $this->assertInstanceOf(IdentityInterface::class, $this->user);
    }

    public function testSetIdReturnsIdentityInterface(): void
    {
        $result = $this->user->setId(123);
        $this->assertInstanceOf(IdentityInterface::class, $result);
    }

    public function testSetEmailReturnsIdentityInterface(): void
    {
        $result = $this->user->setEmail('test@example.com');
        $this->assertInstanceOf(IdentityInterface::class, $result);
    }

    public function testGetNameReturnsFullName(): void
    {
        $this->user->setFullName('John Doe');
        $this->assertEquals('John Doe', $this->user->getName());
    }

    public function testSetNameSetsFullName(): void
    {
        $result = $this->user->setName('Jane Doe');

        $this->assertInstanceOf(IdentityInterface::class, $result);
        $this->assertEquals('Jane Doe', $this->user->getFullName());
        $this->assertEquals('Jane Doe', $this->user->getName());
    }

    public function testSetRolesReturnsIdentityInterface(): void
    {
        $result = $this->user->setRoles('admin,user');
        $this->assertInstanceOf(IdentityInterface::class, $result);
    }

    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('Administrator');

        $this->user->addRole($role);

        $this->assertTrue($this->user->hasRole('Administrator'));
        $this->assertTrue($this->user->hasRole('administrator')); // case insensitive
    }

    public function testHasRoleReturnsFalseWhenRoleDoesNotExist(): void
    {
        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('User');

        $this->user->addRole($role);

        $this->assertFalse($this->user->hasRole('Administrator'));
    }

    public function testHasRoleReturnsFalseWhenNoRoles(): void
    {
        $this->assertFalse($this->user->hasRole('Administrator'));
    }
}
