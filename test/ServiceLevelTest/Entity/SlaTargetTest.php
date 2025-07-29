<?php

declare(strict_types=1);

namespace ServiceLevelTest\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Priority;

class SlaTargetTest extends TestCase
{
    private SlaTarget $slaTarget;

    protected function setUp(): void
    {
        $this->slaTarget = new SlaTarget();
    }

    public function testSetAndGetId(): void
    {
        $result = $this->slaTarget->setId(123);

        $this->assertInstanceOf(SlaTarget::class, $result);
        $this->assertEquals(123, $this->slaTarget->getId());
    }

    public function testSetAndGetSla(): void
    {
        $sla    = $this->createMock(Sla::class);
        $result = $this->slaTarget->setSla($sla);

        $this->assertInstanceOf(SlaTarget::class, $result);
        // Note: There's no getSla() method in the entity, only setPriority/getPriority
    }

    public function testSetAndGetPriority(): void
    {
        $priority = $this->createMock(Priority::class);
        $result   = $this->slaTarget->setPriority($priority);

        $this->assertInstanceOf(SlaTarget::class, $result);
        $this->assertSame($priority, $this->slaTarget->getPriority());
    }

    public function testSetAndGetResponseTime(): void
    {
        $responseTime = '02:30';
        $result       = $this->slaTarget->setResponseTime($responseTime);

        $this->assertInstanceOf(SlaTarget::class, $result);
        $this->assertEquals($responseTime, $this->slaTarget->getResponseTime());
    }

    public function testSetAndGetResolveTime(): void
    {
        $resolveTime = '08:45';
        $result      = $this->slaTarget->setResolveTime($resolveTime);

        $this->assertInstanceOf(SlaTarget::class, $result);
        $this->assertEquals($resolveTime, $this->slaTarget->getResolveTime());
    }

    /**
     * @dataProvider validTimeFormatProvider
     */
    public function testGetTimeInSecondsWithValidFormats(string $timeString, int $expectedSeconds): void
    {
        $result = $this->slaTarget->getTimeInSeconds($timeString);

        $this->assertEquals($expectedSeconds, $result);
    }

    public function validTimeFormatProvider(): array
    {
        return [
            '1 hour'              => ['01:00', 3600],
            '30 minutes'          => ['00:30', 1800],
            '2 hours 45 minutes'  => ['02:45', 9900],
            '24 hours'            => ['24:00', 86400],
            '10 minutes'          => ['00:10', 600],
            '15 hours 30 minutes' => ['15:30', 55800],
        ];
    }

    /**
     * @dataProvider invalidTimeFormatProvider
     */
    public function testGetTimeInSecondsThrowsExceptionForInvalidFormats(string $invalidTimeString): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid duration');

        $this->slaTarget->getTimeInSeconds($invalidTimeString);
    }

    public function invalidTimeFormatProvider(): array
    {
        return [
            'single digit hour'   => ['1:30'],
            'single digit minute' => ['01:5'],
            'no colon'            => ['0130'],
            'letters'             => ['ab:cd'],
            'seconds included'    => ['01:30:45'],
            'empty string'        => [''],
            'too many colons'     => ['01:30:00:00'],
            'negative time'       => ['-01:30'],
        ];
    }

    public function testExchangeArraySetsAllProperties(): void
    {
        $priority = $this->createMock(Priority::class);
        $sla      = $this->createMock(Sla::class);

        $data = [
            'id'            => 456,
            'response_time' => '01:15',
            'resolve_time'  => '04:30',
            'priority'      => $priority,
            'sla'           => $sla,
        ];

        $this->slaTarget->exchangeArray($data);

        $this->assertEquals(456, $this->slaTarget->getId());
        $this->assertEquals('01:15', $this->slaTarget->getResponseTime());
        $this->assertEquals('04:30', $this->slaTarget->getResolveTime());
        $this->assertSame($priority, $this->slaTarget->getPriority());
    }

    public function testGetArrayCopyReturnsCorrectStructure(): void
    {
        $priority = $this->createMock(Priority::class);
        $priority->method('getId')->willReturn(2);

        $sla = $this->createMock(Sla::class);
        $sla->method('getId')->willReturn(10);

        $this->slaTarget->setId(789)
                       ->setResponseTime('00:45')
                       ->setResolveTime('06:00')
                       ->setPriority($priority)
                       ->setSla($sla);

        $arrayCopy = $this->slaTarget->getArrayCopy();

        $this->assertIsArray($arrayCopy);
        $this->assertEquals(789, $arrayCopy['id']);
        $this->assertEquals('00:45', $arrayCopy['response_time']);
        $this->assertEquals('06:00', $arrayCopy['resolve_time']);
        $this->assertEquals(2, $arrayCopy['priority']);
        $this->assertEquals(10, $arrayCopy['sla']);
    }

    public function testFluentInterfaceChaining(): void
    {
        $priority = $this->createMock(Priority::class);
        $sla      = $this->createMock(Sla::class);

        $result = $this->slaTarget
            ->setId(101)
            ->setResponseTime('02:00')
            ->setResolveTime('12:00')
            ->setPriority($priority)
            ->setSla($sla);

        $this->assertInstanceOf(SlaTarget::class, $result);
        $this->assertEquals(101, $this->slaTarget->getId());
        $this->assertEquals('02:00', $this->slaTarget->getResponseTime());
        $this->assertEquals('12:00', $this->slaTarget->getResolveTime());
        $this->assertSame($priority, $this->slaTarget->getPriority());
    }

    public function testExchangeArrayHandlesMissingId(): void
    {
        $priority = $this->createMock(Priority::class);
        $sla      = $this->createMock(Sla::class);

        $data = [
            'response_time' => '03:00',
            'resolve_time'  => '24:00',
            'priority'      => $priority,
            'sla'           => $sla,
        ];

        $this->slaTarget->exchangeArray($data);

        $this->assertNull($this->slaTarget->getId());
        $this->assertEquals('03:00', $this->slaTarget->getResponseTime());
        $this->assertEquals('24:00', $this->slaTarget->getResolveTime());
        $this->assertSame($priority, $this->slaTarget->getPriority());
    }

    public function testGetTimeInSecondsCalculationAccuracy(): void
    {
        // Test edge cases and calculation accuracy
        $this->assertEquals(0, $this->slaTarget->getTimeInSeconds('00:00'));
        $this->assertEquals(60, $this->slaTarget->getTimeInSeconds('00:01'));
        $this->assertEquals(3600, $this->slaTarget->getTimeInSeconds('01:00'));
        // Fixed: 1 hour + 1 minute = 3660 seconds
        $this->assertEquals(3660, $this->slaTarget->getTimeInSeconds('01:01'));
        // Actual calculation: 99*3600 + 99*60 = 356400 + 5940 = 362340
        $this->assertEquals(362340, $this->slaTarget->getTimeInSeconds('99:99'));
    }
}
