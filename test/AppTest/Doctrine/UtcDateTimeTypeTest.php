<?php

declare(strict_types=1);

namespace AppTest\Doctrine;

use App\Doctrine\UtcDateTimeType;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\TestCase;

/**
 * Every ticket timestamp goes through this type. Whatever timezone a value
 * arrives in, it must store the same instant as UTC.
 */
class UtcDateTimeTypeTest extends TestCase
{
    private UtcDateTimeType $type;
    private MySQLPlatform $platform;

    protected function setUp(): void
    {
        $this->type     = new UtcDateTimeType();
        $this->platform = new MySQLPlatform();
    }

    public function testConvertsLocalTimeToUtcOnWrite(): void
    {
        // 15:00 BST is 14:00 UTC
        $london = new DateTime('2026-07-01 15:00:00', new DateTimeZone('Europe/London'));

        $this->assertSame(
            '2026-07-01 14:00:00',
            $this->type->convertToDatabaseValue($london, $this->platform)
        );
    }

    public function testLeavesUtcValuesAlone(): void
    {
        $utc = new DateTime('2026-07-01 14:00:00', new DateTimeZone('UTC'));

        $this->assertSame(
            '2026-07-01 14:00:00',
            $this->type->convertToDatabaseValue($utc, $this->platform)
        );
    }

    public function testDoesNotMutateItsArgument(): void
    {
        $london = new DateTime('2026-07-01 15:00:00', new DateTimeZone('Europe/London'));

        $this->type->convertToDatabaseValue($london, $this->platform);

        $this->assertSame('Europe/London', $london->getTimezone()->getName());
        $this->assertSame('2026-07-01 15:00:00', $london->format('Y-m-d H:i:s'));
    }

    public function testConvertsImmutableValues(): void
    {
        $london = new DateTimeImmutable('2026-07-01 15:00:00', new DateTimeZone('Europe/London'));

        $this->assertSame(
            '2026-07-01 14:00:00',
            $this->type->convertToDatabaseValue($london, $this->platform)
        );
    }

    public function testConvertsCarbonValues(): void
    {
        $london = Carbon::create(2026, 7, 1, 15, 0, 0, 'Europe/London');

        $this->assertSame(
            '2026-07-01 14:00:00',
            $this->type->convertToDatabaseValue($london, $this->platform)
        );
    }

    public function testReadsStoredValuesAsUtc(): void
    {
        $value = $this->type->convertToPHPValue('2026-07-01 14:00:00', $this->platform);

        $this->assertInstanceOf(DateTime::class, $value);
        $this->assertSame('UTC', $value->getTimezone()->getName());
        $this->assertSame('2026-07-01 14:00:00', $value->format('Y-m-d H:i:s'));
    }

    public function testNullRoundTrips(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }
}
