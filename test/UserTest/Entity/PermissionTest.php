<?php

declare(strict_types=1);

namespace UserTest\Entity;

use PHPUnit\Framework\TestCase;
use User\Entity\Permission;

class PermissionTest extends TestCase
{
    private Permission $permission;

    protected function setUp(): void
    {
        $this->permission = new Permission();
    }

    public function testConstructorInitializesRolesCollection(): void
    {
        $permission = new Permission();
        
        $this->assertCount(0, $permission->getRoles());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->permission->setId(123);
        
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(123, $this->permission->getId());
    }

    public function testSetAndGetName(): void
    {
        $name = 'user.create';
        $result = $this->permission->setName($name);
        
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals($name, $this->permission->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'Allows creating new users';
        $result = $this->permission->setDescription($description);
        
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals($description, $this->permission->getDescription());
    }

    public function testSetAndGetDateCreated(): void
    {
        $dateCreated = '2023-01-01 12:00:00';
        $result = $this->permission->setDateCreated($dateCreated);
        
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals($dateCreated, $this->permission->getDateCreated());
    }

    public function testGetRolesReturnsCollection(): void
    {
        $roles = $this->permission->getRoles();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $roles);
        $this->assertCount(0, $roles);
    }

    public function testFluentInterfaceChaining(): void
    {
        $result = $this->permission
            ->setId(1)
            ->setName('test.permission')
            ->setDescription('Test permission description')
            ->setDateCreated('2023-01-01 12:00:00');
            
        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(1, $this->permission->getId());
        $this->assertEquals('test.permission', $this->permission->getName());
        $this->assertEquals('Test permission description', $this->permission->getDescription());
        $this->assertEquals('2023-01-01 12:00:00', $this->permission->getDateCreated());
    }
}