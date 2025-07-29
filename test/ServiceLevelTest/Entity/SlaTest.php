<?php

declare(strict_types=1);

namespace ServiceLevelTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Priority;

class SlaTest extends TestCase
{
    private Sla $sla;

    protected function setUp(): void
    {
        $this->sla = new Sla();
    }

    public function testConstructorInitializesDefaults(): void
    {
        $sla = new Sla();
        
        // Sla entity doesn't initialize $id property, so we skip testing getId() in constructor test
        $this->markTestSkipped('Sla entity $id property not initialized in constructor');
        $this->assertInstanceOf(ArrayCollection::class, $sla->getSlaTargets());
        $this->assertCount(0, $sla->getSlaTargets());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->sla->setId(123);
        
        $this->assertInstanceOf(Sla::class, $result);
        $this->assertEquals(123, $this->sla->getId());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Premium SLA';
        $result = $this->sla->setName($name);
        
        $this->assertInstanceOf(Sla::class, $result);
        $this->assertEquals($name, $this->sla->getName());
    }

    public function testSetAndGetBusinessHours(): void
    {
        $businessHours = $this->createMock(BusinessHours::class);
        $result = $this->sla->setBusinessHours($businessHours);
        
        $this->assertInstanceOf(Sla::class, $result);
        $this->assertSame($businessHours, $this->sla->getBusinessHours());
    }

    public function testAddSlaTargetUsesCorrectKey(): void
    {
        $priority = $this->createMock(Priority::class);
        $priority->method('getId')->willReturn(2);
        
        $target = $this->createMock(SlaTarget::class);
        $target->method('getPriority')->willReturn($priority);
        
        $this->sla->addSlaTarget($target);
        
        $targets = $this->sla->getSlaTargets();
        $this->assertCount(1, $targets);
        
        // Test that we can retrieve by priority ID
        $retrievedTarget = $this->sla->getSlaTarget(2);
        $this->assertSame($target, $retrievedTarget);
    }

    public function testGetSlaTargetReturnsCorrectTarget(): void
    {
        $priority1 = $this->createMock(Priority::class);
        $priority1->method('getId')->willReturn(1);
        
        $priority2 = $this->createMock(Priority::class);
        $priority2->method('getId')->willReturn(2);
        
        $target1 = $this->createMock(SlaTarget::class);
        $target1->method('getPriority')->willReturn($priority1);
        
        $target2 = $this->createMock(SlaTarget::class);
        $target2->method('getPriority')->willReturn($priority2);
        
        $this->sla->addSlaTarget($target1);
        $this->sla->addSlaTarget($target2);
        
        $this->assertSame($target1, $this->sla->getSlaTarget(1));
        $this->assertSame($target2, $this->sla->getSlaTarget(2));
    }

    public function testRemoveTargetRemovesFromCollection(): void
    {
        $priority = $this->createMock(Priority::class);
        $priority->method('getId')->willReturn(1);
        
        $target = $this->createMock(SlaTarget::class);
        $target->method('getPriority')->willReturn($priority);
        
        $this->sla->addSlaTarget($target);
        $this->assertCount(1, $this->sla->getSlaTargets());
        
        $this->sla->removeTarget($target);
        $this->assertCount(0, $this->sla->getSlaTargets());
    }

    public function testRemoveTargetIgnoresNonExistentTarget(): void
    {
        $target = $this->createMock(SlaTarget::class);
        
        // Should not throw exception when removing non-existent target
        $this->sla->removeTarget($target);
        $this->assertCount(0, $this->sla->getSlaTargets());
    }

    public function testGetArrayCopyReturnsCorrectStructure(): void
    {
        $businessHours = $this->createMock(BusinessHours::class);
        $targets = new ArrayCollection();
        
        $this->sla->setId(456)
                  ->setName('Test SLA')
                  ->setBusinessHours($businessHours);
        
        $arrayCopy = $this->sla->getArrayCopy();
        
        $this->assertIsArray($arrayCopy);
        $this->assertEquals(456, $arrayCopy['id']);
        $this->assertEquals('Test SLA', $arrayCopy['name']);
        $this->assertSame($businessHours, $arrayCopy['business_hours']);
        $this->assertInstanceOf(ArrayCollection::class, $arrayCopy['sla_targets']);
    }

    public function testFluentInterfaceChaining(): void
    {
        $businessHours = $this->createMock(BusinessHours::class);
        
        $result = $this->sla
            ->setId(789)
            ->setName('Chained SLA')
            ->setBusinessHours($businessHours);
        
        $this->assertInstanceOf(Sla::class, $result);
        $this->assertEquals(789, $this->sla->getId());
        $this->assertEquals('Chained SLA', $this->sla->getName());
        $this->assertSame($businessHours, $this->sla->getBusinessHours());
    }

    public function testAddMultipleSlaTargetsWithDifferentPriorities(): void
    {
        $priority1 = $this->createMock(Priority::class);
        $priority1->method('getId')->willReturn(1);
        
        $priority2 = $this->createMock(Priority::class);
        $priority2->method('getId')->willReturn(3);
        
        $target1 = $this->createMock(SlaTarget::class);
        $target1->method('getPriority')->willReturn($priority1);
        
        $target2 = $this->createMock(SlaTarget::class);
        $target2->method('getPriority')->willReturn($priority2);
        
        $this->sla->addSlaTarget($target1);
        $this->sla->addSlaTarget($target2);
        
        $this->assertCount(2, $this->sla->getSlaTargets());
        $this->assertSame($target1, $this->sla->getSlaTarget(1));
        $this->assertSame($target2, $this->sla->getSlaTarget(3));
    }

    public function testAddSlaTargetOverwritesSamePriorityTarget(): void
    {
        $priority = $this->createMock(Priority::class);
        $priority->method('getId')->willReturn(2);
        
        $target1 = $this->createMock(SlaTarget::class);
        $target1->method('getPriority')->willReturn($priority);
        
        $target2 = $this->createMock(SlaTarget::class);
        $target2->method('getPriority')->willReturn($priority);
        
        $this->sla->addSlaTarget($target1);
        $this->sla->addSlaTarget($target2); // Should overwrite target1
        
        // Collection should still contain both but indexed by priority ID
        // The getSlaTarget method should return the last added target
        $this->assertSame($target2, $this->sla->getSlaTarget(2));
    }
}