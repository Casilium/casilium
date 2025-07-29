<?php

declare(strict_types=1);

namespace UserAuthenticationTest\Entity;

use PHPUnit\Framework\TestCase;
use UserAuthentication\Entity\Identity;
use UserAuthentication\Entity\IdentityInterface;

class IdentityTest extends TestCase
{
    private Identity $identity;

    protected function setUp(): void
    {
        $this->identity = new Identity();
    }

    public function testImplementsIdentityInterface(): void
    {
        $this->assertInstanceOf(IdentityInterface::class, $this->identity);
    }

    public function testSetAndGetId(): void
    {
        $result = $this->identity->setId(123);

        $this->assertInstanceOf(Identity::class, $result);
        $this->assertEquals(123, $this->identity->getId());
    }

    public function testGetIdReturnsNullInitially(): void
    {
        $this->assertNull($this->identity->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $email  = 'test@example.com';
        $result = $this->identity->setEmail($email);

        $this->assertInstanceOf(Identity::class, $result);
        $this->assertEquals($email, $this->identity->getEmail());
    }

    public function testGetEmailReturnsNullInitially(): void
    {
        $this->assertNull($this->identity->getEmail());
    }

    public function testSetAndGetName(): void
    {
        $name   = 'John Doe';
        $result = $this->identity->setName($name);

        $this->assertInstanceOf(Identity::class, $result);
        $this->assertEquals($name, $this->identity->getName());
    }

    public function testGetNameReturnsNullInitially(): void
    {
        $this->assertNull($this->identity->getName());
    }

    public function testSetRolesWithSingleRole(): void
    {
        $result = $this->identity->setRoles('admin');

        $this->assertInstanceOf(Identity::class, $result);
        $this->assertTrue($this->identity->hasRole('admin'));
    }

    public function testSetRolesWithMultipleRoles(): void
    {
        $this->identity->setRoles('admin,user,moderator');

        $this->assertTrue($this->identity->hasRole('admin'));
        $this->assertTrue($this->identity->hasRole('user'));
        $this->assertTrue($this->identity->hasRole('moderator'));
    }

    public function testHasRoleWithExistingRole(): void
    {
        $this->identity->setRoles('admin,user');

        $this->assertTrue($this->identity->hasRole('admin'));
        $this->assertTrue($this->identity->hasRole('user'));
    }

    public function testHasRoleWithNonExistentRole(): void
    {
        $this->identity->setRoles('admin,user');

        $this->assertFalse($this->identity->hasRole('moderator'));
        $this->assertFalse($this->identity->hasRole('guest'));
    }

    public function testHasRoleIsCaseInsensitive(): void
    {
        $this->identity->setRoles('Admin,USER');

        $this->assertTrue($this->identity->hasRole('admin'));
        $this->assertTrue($this->identity->hasRole('ADMIN'));
        $this->assertTrue($this->identity->hasRole('user'));
        $this->assertTrue($this->identity->hasRole('User'));
    }

    public function testHasRoleWithEmptyRoles(): void
    {
        $this->assertFalse($this->identity->hasRole('admin'));
    }

    public function testSetRolesWithEmptyString(): void
    {
        $this->identity->setRoles('');

        $this->assertTrue($this->identity->hasRole(''));
        $this->assertFalse($this->identity->hasRole('admin'));
    }

    public function testFluentInterfaceChaining(): void
    {
        $result = $this->identity
            ->setId(123)
            ->setEmail('test@example.com')
            ->setName('Test User')
            ->setRoles('admin,user');

        $this->assertInstanceOf(Identity::class, $result);
        $this->assertEquals(123, $this->identity->getId());
        $this->assertEquals('test@example.com', $this->identity->getEmail());
        $this->assertEquals('Test User', $this->identity->getName());
        $this->assertTrue($this->identity->hasRole('admin'));
        $this->assertTrue($this->identity->hasRole('user'));
    }

    /**
     * @dataProvider roleDataProvider
     */
    public function testHasRoleWithVariousInputs(string $roles, string $searchRole, bool $expected): void
    {
        $this->identity->setRoles($roles);
        $this->assertEquals($expected, $this->identity->hasRole($searchRole));
    }

    public function roleDataProvider(): array
    {
        return [
            'single exact match'        => ['admin', 'admin', true],
            'single no match'           => ['admin', 'user', false],
            'multiple with match'       => ['admin,user,guest', 'user', true],
            'multiple no match'         => ['admin,user,guest', 'moderator', false],
            'case insensitive match'    => ['Admin,User', 'admin', true],
            'case insensitive no match' => ['Admin,User', 'guest', false],
            'empty roles'               => ['', 'admin', false],
            'whitespace in roles'       => ['admin, user, guest', ' user', true],
        ];
    }
}
