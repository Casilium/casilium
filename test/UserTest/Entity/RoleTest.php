<?php

declare(strict_types=1);

namespace UserTest\Entity;

use PHPUnit\Framework\TestCase;
use User\Entity\Role;

class RoleTest extends TestCase
{
    private Role $role;

    protected function setUp(): void
    {
        $this->role = new Role();
    }

    public function testConstructorInitializesCollections(): void
    {
        $role = new Role();

        $this->assertCount(0, $role->getParentRoles());
        $this->assertCount(0, $role->getChildRoles());
        $this->assertCount(0, $role->getPermissions());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->role->setId(123);

        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals(123, $this->role->getId());
    }

    public function testSetAndGetName(): void
    {
        $name   = 'Administrator';
        $result = $this->role->setName($name);

        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals($name, $this->role->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'System administrator role';
        $result      = $this->role->setDescription($description);

        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals($description, $this->role->getDescription());
    }

    public function testSetAndGetDateCreated(): void
    {
        $dateCreated = '2023-01-01 12:00:00';
        $result      = $this->role->setDateCreated($dateCreated);

        $this->assertInstanceOf(Role::class, $result);
        $this->assertEquals($dateCreated, $this->role->getDateCreated());
    }

    public function testAddParentWithValidRole(): void
    {
        $parentRole = new Role();
        $parentRole->setId(1);
        $this->role->setId(2);

        $result = $this->role->addParent($parentRole);

        $this->assertTrue($result);
        $this->assertCount(1, $this->role->getParentRoles());
        $this->assertCount(1, $parentRole->getChildRoles());
        $this->assertTrue($this->role->getParentRoles()->contains($parentRole));
        $this->assertTrue($parentRole->getChildRoles()->contains($this->role));
    }

    public function testAddParentWithSameRoleReturnsFalse(): void
    {
        $this->role->setId(1);

        $result = $this->role->addParent($this->role);

        $this->assertFalse($result);
        $this->assertCount(0, $this->role->getParentRoles());
    }

    public function testAddParentWithExistingParentReturnsFalse(): void
    {
        $parentRole = new Role();
        $parentRole->setId(1);
        $this->role->setId(2);

        // Add parent first time
        $this->role->addParent($parentRole);

        // Try to add same parent again
        $result = $this->role->addParent($parentRole);

        $this->assertFalse($result);
        $this->assertCount(1, $this->role->getParentRoles());
    }

    public function testHasParentWithExistingParent(): void
    {
        $parentRole = new Role();
        $parentRole->setId(1);
        $this->role->setId(2);

        $this->role->addParent($parentRole);

        $this->assertTrue($this->role->hasParent($parentRole));
    }

    public function testHasParentWithNonExistingParent(): void
    {
        $parentRole = new Role();
        $parentRole->setId(1);

        $this->assertFalse($this->role->hasParent($parentRole));
    }

    public function testClearParentRoles(): void
    {
        $parentRole1 = new Role();
        $parentRole1->setId(1);
        $parentRole2 = new Role();
        $parentRole2->setId(2);
        $this->role->setId(3);

        $this->role->addParent($parentRole1);
        $this->role->addParent($parentRole2);

        $this->assertCount(2, $this->role->getParentRoles());

        $result = $this->role->clearParentRoles();

        $this->assertInstanceOf(Role::class, $result);
        $this->assertCount(0, $this->role->getParentRoles());
    }

    public function testRoleHierarchyBidirectionalRelationship(): void
    {
        $adminRole = new Role();
        $adminRole->setId(1)->setName('Admin');

        $userRole = new Role();
        $userRole->setId(2)->setName('User');

        $userRole->addParent($adminRole);

        // Verify bidirectional relationship
        $this->assertTrue($userRole->hasParent($adminRole));
        $this->assertTrue($adminRole->getChildRoles()->contains($userRole));
        $this->assertCount(1, $userRole->getParentRoles());
        $this->assertCount(1, $adminRole->getChildRoles());
    }
}
