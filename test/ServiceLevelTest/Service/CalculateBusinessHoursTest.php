<?php

declare(strict_types=1);

namespace ServiceLevelTest\Service;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Service\CalculateBusinessHours;

class CalculateBusinessHoursTest extends TestCase
{
    private BusinessHours $businessHours;
    private CalculateBusinessHours $calculator;

    protected function setUp(): void
    {
        // Create standard Mon-Fri 9-5 business hours for testing
        $this->businessHours = new BusinessHours();
        
        // Use reflection to set the id property for testing
        $reflection = new \ReflectionClass($this->businessHours);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->businessHours, 1);
        
        $this->businessHours->setName('Standard Business Hours')
                           ->setTimezone('UTC')
                           ->setMonActive(true)->setMonStart('09:00')->setMonEnd('17:00')
                           ->setTueActive(true)->setTueStart('09:00')->setTueEnd('17:00')
                           ->setWedActive(true)->setWedStart('09:00')->setWedEnd('17:00')
                           ->setThuActive(true)->setThuStart('09:00')->setThuEnd('17:00')
                           ->setFriActive(true)->setFriStart('09:00')->setFriEnd('17:00')
                           ->setSatActive(false)->setSatStart('09:00')->setSatEnd('17:00')
                           ->setSunActive(false)->setSunStart('09:00')->setSunEnd('17:00');
        
        $this->calculator = new CalculateBusinessHours($this->businessHours);
    }

    public function testAddHoursToWithinSameWorkingDay(): void
    {
        // Test adding 2 hours on a Monday at 10:00 AM
        $startDate = Carbon::create(2023, 1, 2, 10, 0, 0, 'UTC'); // Monday
        $duration = '02:00';
        
        $result = $this->calculator->addHoursTo($startDate, $duration);
        
        // Should be 12:00 PM same day
        $this->assertEquals('2023-01-02 12:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddHoursToSpanningMultipleDays(): void
    {
        // Test adding 8 hours on Friday at 3:00 PM (should spill into Monday)
        $startDate = Carbon::create(2023, 1, 6, 15, 0, 0, 'UTC'); // Friday
        $duration = '08:00';
        
        $result = $this->calculator->addHoursTo($startDate, $duration);
        
        // Should skip weekend and continue on Monday
        // Friday 15:00 + 2 hours = Friday 17:00 (end of day)
        // Remaining 6 hours should be added to Monday starting at 09:00
        // Monday 09:00 + 6 hours = Monday 15:00
        $this->assertEquals('2023-01-09 15:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddHoursToOnWeekend(): void
    {
        // Test starting on Saturday (inactive day)
        $startDate = Carbon::create(2023, 1, 7, 10, 0, 0, 'UTC'); // Saturday
        $duration = '04:00';
        
        $result = $this->calculator->addHoursTo($startDate, $duration);
        
        // Should skip to Monday and add 4 hours from 09:00
        // Monday 09:00 + 4 hours = Monday 13:00
        $this->assertEquals('2023-01-09 13:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddHoursToBeforeBusinessHours(): void
    {
        // Test starting at 6:00 AM (before business hours)
        $startDate = Carbon::create(2023, 1, 2, 6, 0, 0, 'UTC'); // Monday
        $duration = '02:00';
        
        $result = $this->calculator->addHoursTo($startDate, $duration);
        
        // Should start from 09:00 and add 2 hours = 11:00
        $this->assertEquals('2023-01-02 11:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddHoursToAfterBusinessHours(): void
    {
        // Test starting at 8:00 PM (after business hours)
        $startDate = Carbon::create(2023, 1, 2, 20, 0, 0, 'UTC'); // Monday
        $duration = '03:00';
        
        $result = $this->calculator->addHoursTo($startDate, $duration);
        
        // Should move to next day (Tuesday) at 09:00 and add 3 hours = 12:00
        $this->assertEquals('2023-01-03 12:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddMinutesToWithinSameHour(): void
    {
        // Test adding 30 minutes on Monday at 10:00 AM
        $startDate = Carbon::create(2023, 1, 2, 10, 0, 0, 'UTC'); // Monday
        
        $result = $this->calculator->addMinutesTo($startDate, 30);
        
        $this->assertEquals('2023-01-02 10:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddMinutesToSpanningDays(): void
    {
        // Test adding 120 minutes (2 hours) on Friday at 4:30 PM
        $startDate = Carbon::create(2023, 1, 6, 16, 30, 0, 'UTC'); // Friday
        
        $result = $this->calculator->addMinutesTo($startDate, 120);
        
        // Should skip weekend: Friday 16:30 + 30 minutes = Friday 17:00
        // Remaining 90 minutes should be added to Monday starting at 09:00
        // Monday 09:00 + 90 minutes = Monday 10:30
        $this->assertEquals('2023-01-09 10:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddMinutesToOnWeekend(): void
    {
        // Test starting on Sunday (inactive day)
        $startDate = Carbon::create(2023, 1, 8, 10, 0, 0, 'UTC'); // Sunday
        
        $result = $this->calculator->addMinutesTo($startDate, 60);
        
        // Should skip to Monday and add 60 minutes from 09:00 = 10:00
        $this->assertEquals('2023-01-09 10:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testGetHoursBetweenDatesWithOptions(): void
    {
        $options = ['start' => '09:00', 'end' => '17:00'];
        $calculator = new CalculateBusinessHours($this->businessHours, $options);
        
        $from = Carbon::create(2023, 1, 2, 9, 0, 0, 'UTC'); // Monday 9 AM
        $to = Carbon::create(2023, 1, 2, 17, 0, 0, 'UTC');   // Monday 5 PM
        
        $result = $calculator->getHoursBetweenDates($from, $to);
        
        // Should be 8 hours (10,11,12,13,14,15,16,17 - excluding start hour 9, including 17)
        $this->assertEquals(8, $result);
    }

    public function testGetHoursBetweenDatesReturnsZeroWhenToIsBeforeFrom(): void
    {
        $options = ['start' => '09:00', 'end' => '17:00'];
        $calculator = new CalculateBusinessHours($this->businessHours, $options);
        
        $from = Carbon::create(2023, 1, 2, 17, 0, 0, 'UTC');
        $to = Carbon::create(2023, 1, 2, 9, 0, 0, 'UTC');
        
        $result = $calculator->getHoursBetweenDates($from, $to);
        
        $this->assertEquals(0, $result);
    }

    public function testGetHoursFromDate(): void
    {
        Carbon::setTestNow(Carbon::create(2023, 1, 2, 15, 0, 0, 'UTC'));
        
        $options = ['start' => '09:00', 'end' => '17:00'];
        $calculator = new CalculateBusinessHours($this->businessHours, $options);
        
        $start = Carbon::create(2023, 1, 2, 9, 0, 0, 'UTC');
        
        $result = $calculator->getHoursFromDate($start);
        
        // From 9 AM to 3 PM = 6 hours (10,11,12,13,14,15)
        $this->assertEquals(6, $result);
        
        Carbon::setTestNow(); // Reset
    }

    public function testGetHoursFromFloat(): void
    {
        $result = CalculateBusinessHours::getHoursFromFloat(2.75);
        $this->assertEquals(3, $result); // Rounded up
        
        $result = CalculateBusinessHours::getHoursFromFloat(2.25);
        $this->assertEquals(2, $result); // Rounded down
    }

    public function testGetMinutesFromFloat(): void
    {
        $result = CalculateBusinessHours::getMinutesFromFloat(2.75);
        $this->assertEquals(75, $result);
        
        $result = CalculateBusinessHours::getMinutesFromFloat(1.50);
        $this->assertEquals(50, $result);
        
        $result = CalculateBusinessHours::getMinutesFromFloat(3.0);
        $this->assertEquals(0, $result);
    }

    public function testCustomBusinessHoursConfiguration(): void
    {
        // Create custom business hours (Tuesday-Thursday, 10 AM - 6 PM)
        $customBusinessHours = new BusinessHours();
        
        // Use reflection to set the id property for testing
        $reflection = new \ReflectionClass($customBusinessHours);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($customBusinessHours, 2);
        $customBusinessHours->setName('Custom Hours')
                           ->setTimezone('UTC')
                           ->setMonActive(false)->setMonStart('10:00')->setMonEnd('18:00')
                           ->setTueActive(true)->setTueStart('10:00')->setTueEnd('18:00')
                           ->setWedActive(true)->setWedStart('10:00')->setWedEnd('18:00')
                           ->setThuActive(true)->setThuStart('10:00')->setThuEnd('18:00')
                           ->setFriActive(false)->setFriStart('10:00')->setFriEnd('18:00')
                           ->setSatActive(false)->setSatStart('10:00')->setSatEnd('18:00')
                           ->setSunActive(false)->setSunStart('10:00')->setSunEnd('18:00');
        
        $calculator = new CalculateBusinessHours($customBusinessHours);
        
        // Test adding hours on Monday (inactive) should move to Tuesday
        $startDate = Carbon::create(2023, 1, 2, 14, 0, 0, 'UTC'); // Monday
        $duration = '02:00';
        
        $result = $calculator->addHoursTo($startDate, $duration);
        
        // Should skip to Tuesday 10:00 and add 2 hours = Tuesday 12:00
        $this->assertEquals('2023-01-03 12:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testTimezoneHandling(): void
    {
        $this->businessHours->setTimezone('America/New_York');
        $calculator = new CalculateBusinessHours($this->businessHours);
        
        $startDate = Carbon::create(2023, 1, 2, 10, 0, 0, 'UTC'); // Monday in UTC
        $duration = '01:00';
        
        $result = $calculator->addHoursTo($startDate, $duration);
        
        // Result should be in the business hours timezone
        $this->assertEquals('America/New_York', $result->getTimezone()->getName());
    }

    /**
     * Test for the known bug mentioned: tickets becoming due on weekends
     * This test documents the current behavior and should fail when the bug is fixed
     */
    public function testKnownWeekendDueBug(): void
    {
        // This test should be enabled when investigating/fixing the weekend due date bug
        
        // This test should be enabled when investigating/fixing the weekend due date bug
        // Test scenario: Create ticket on Friday afternoon with short SLA
        // Expected: Due date should be Monday morning
        // Actual bug: Due date might be Saturday or Sunday
        
        $fridayAfternoon = Carbon::create(2023, 1, 6, 16, 0, 0, 'UTC'); // Friday 4 PM
        $duration = '04:00'; // 4 hours
        
        $result = $this->calculator->addHoursTo($fridayAfternoon, $duration);
        
        // Should be Monday 12:00 (Friday 16:00 + 1 hour = 17:00, then skip weekend, Monday 09:00 + 3 hours = 12:00)
        $this->assertEquals('2023-01-09 12:00:00', $result->format('Y-m-d H:i:s'));
        $this->assertFalse($result->isWeekend(), 'Due date should not fall on weekend');
    }

    public function testBusinessHoursToArrayConversion(): void
    {
        // Test that the internal conversion works correctly
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('businessHoursToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->calculator, $this->businessHours);
        
        // Check structure contains expected days
        $this->assertArrayHasKey('mon', $result);
        $this->assertArrayHasKey('tue', $result);
        $this->assertArrayHasKey('wed', $result);
        $this->assertArrayHasKey('thu', $result);
        $this->assertArrayHasKey('fri', $result);
        
        // Check Monday has correct default values
        $this->assertTrue($result['mon']['active']);
        $this->assertEquals(9, $result['mon']['startHour']);
        $this->assertEquals(0, $result['mon']['startMinute']);
        $this->assertEquals(17, $result['mon']['endHour']);
        $this->assertEquals(0, $result['mon']['endMinute']);
    }

    public function testGetHoursAndMinutesFromStringValidFormats(): void
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('getHoursAndMinutesFromString');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->calculator, '09:30');
        $this->assertEquals(['hours' => '09', 'minutes' => '30'], $result);
        
        $result = $method->invoke($this->calculator, '17:00');
        $this->assertEquals(['hours' => '17', 'minutes' => '00'], $result);
        
        $result = $method->invoke($this->calculator, null);
        $this->assertNull($result);
        
        $result = $method->invoke($this->calculator, 'invalid');
        $this->assertNull($result);
    }
}