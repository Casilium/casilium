<?php

declare(strict_types=1);

namespace TicketTest\InputFilter;

use PHPUnit\Framework\TestCase;
use Ticket\InputFilter\SearchTicketInputFilter;

use function str_repeat;

class SearchTicketInputFilterTest extends TestCase
{
    private SearchTicketInputFilter $inputFilter;

    protected function setUp(): void
    {
        $this->inputFilter = new SearchTicketInputFilter();
        $this->inputFilter->init();
    }

    public function testEmptyDataIsValid(): void
    {
        $this->inputFilter->setData([]);
        $this->assertTrue($this->inputFilter->isValid());
    }

    public function testValidSearchText(): void
    {
        $this->inputFilter->setData(['search_text' => '  printer issue  ']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('printer issue', $this->inputFilter->getValue('search_text'));
    }

    public function testSearchTextStripsHtmlTags(): void
    {
        $this->inputFilter->setData(['search_text' => '<script>alert("xss")</script>printer']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('alert("xss")printer', $this->inputFilter->getValue('search_text'));
    }

    public function testSearchTextExceedingMaxLengthIsInvalid(): void
    {
        $this->inputFilter->setData(['search_text' => str_repeat('a', 256)]);
        $this->assertFalse($this->inputFilter->isValid());
        $this->assertArrayHasKey('search_text', $this->inputFilter->getMessages());
    }

    public function testValidOrganisationUuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $this->inputFilter->setData(['organisation_uuid' => $uuid]);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame($uuid, $this->inputFilter->getValue('organisation_uuid'));
    }

    public function testInvalidOrganisationUuid(): void
    {
        $this->inputFilter->setData(['organisation_uuid' => 'not-a-uuid']);
        $this->assertFalse($this->inputFilter->isValid());
        $this->assertArrayHasKey('organisation_uuid', $this->inputFilter->getMessages());
    }

    public function testOrganisationUuidStripsHtmlTagsBeforeValidation(): void
    {
        // StripTags removes <b></b> leaving a valid UUID, so validation passes
        $this->inputFilter->setData(['organisation_uuid' => '<b>550e8400-e29b-41d4-a716-446655440000</b>']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $this->inputFilter->getValue('organisation_uuid'));
    }

    public function testValidContactId(): void
    {
        $this->inputFilter->setData(['contact_id' => '42']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame(42, $this->inputFilter->getValue('contact_id'));
    }

    public function testNonNumericContactIdCoercesToZero(): void
    {
        // ToInt filter converts 'abc' to 0 before Digits validator runs, so it passes
        // The handler excludes 0 values from filters
        $this->inputFilter->setData(['contact_id' => 'abc']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame(0, $this->inputFilter->getValue('contact_id'));
    }

    public function testValidQueueId(): void
    {
        $this->inputFilter->setData(['queue_id' => '5']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame(5, $this->inputFilter->getValue('queue_id'));
    }

    public function testNonNumericQueueIdCoercesToZero(): void
    {
        // ToInt filter converts 'abc' to 0 before Digits validator runs, so it passes
        // The handler excludes 0 values from filters
        $this->inputFilter->setData(['queue_id' => 'abc']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame(0, $this->inputFilter->getValue('queue_id'));
    }

    public function testValidDateFrom(): void
    {
        $this->inputFilter->setData(['date_from' => '2025-01-15']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('2025-01-15', $this->inputFilter->getValue('date_from'));
    }

    public function testInvalidDateFrom(): void
    {
        $this->inputFilter->setData(['date_from' => 'not-a-date']);
        $this->assertFalse($this->inputFilter->isValid());
        $this->assertArrayHasKey('date_from', $this->inputFilter->getMessages());
    }

    public function testInvalidDateFromFormat(): void
    {
        $this->inputFilter->setData(['date_from' => '15/01/2025']);
        $this->assertFalse($this->inputFilter->isValid());
        $this->assertArrayHasKey('date_from', $this->inputFilter->getMessages());
    }

    public function testValidDateTo(): void
    {
        $this->inputFilter->setData(['date_to' => '2025-12-31']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('2025-12-31', $this->inputFilter->getValue('date_to'));
    }

    public function testInvalidDateTo(): void
    {
        $this->inputFilter->setData(['date_to' => 'not-a-date']);
        $this->assertFalse($this->inputFilter->isValid());
        $this->assertArrayHasKey('date_to', $this->inputFilter->getMessages());
    }

    public function testDateFromTrimsWhitespace(): void
    {
        $this->inputFilter->setData(['date_from' => '  2025-06-15  ']);
        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('2025-06-15', $this->inputFilter->getValue('date_from'));
    }

    public function testAllFieldsValidTogether(): void
    {
        $this->inputFilter->setData([
            'search_text'       => 'network outage',
            'organisation_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'contact_id'        => '10',
            'queue_id'          => '3',
            'date_from'         => '2025-01-01',
            'date_to'           => '2025-12-31',
        ]);

        $this->assertTrue($this->inputFilter->isValid());
        $this->assertSame('network outage', $this->inputFilter->getValue('search_text'));
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $this->inputFilter->getValue('organisation_uuid'));
        $this->assertSame(10, $this->inputFilter->getValue('contact_id'));
        $this->assertSame(3, $this->inputFilter->getValue('queue_id'));
        $this->assertSame('2025-01-01', $this->inputFilter->getValue('date_from'));
        $this->assertSame('2025-12-31', $this->inputFilter->getValue('date_to'));
    }

    public function testMixedValidAndInvalidFields(): void
    {
        $this->inputFilter->setData([
            'search_text'       => 'valid text',
            'organisation_uuid' => 'not-a-uuid',
            'date_from'         => '2025-01-01',
            'date_to'           => 'bad-date',
        ]);

        $this->assertFalse($this->inputFilter->isValid());

        $messages = $this->inputFilter->getMessages();
        $this->assertArrayNotHasKey('search_text', $messages);
        $this->assertArrayHasKey('organisation_uuid', $messages);
        $this->assertArrayNotHasKey('date_from', $messages);
        $this->assertArrayHasKey('date_to', $messages);
    }

    public function testAllFieldsOptional(): void
    {
        $this->inputFilter->setData([
            'search_text'       => '',
            'organisation_uuid' => '',
            'contact_id'        => '',
            'queue_id'          => '',
            'date_from'         => '',
            'date_to'           => '',
        ]);

        $this->assertTrue($this->inputFilter->isValid());
    }
}
