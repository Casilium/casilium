<?php

declare(strict_types=1);

namespace OrganisationContactTest\Validator;

use OrganisationContact\Validator\PhoneNumberValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class PhoneNumberValidatorTest extends TestCase
{
    private PhoneNumberValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PhoneNumberValidator();
    }

    #[DataProvider('validPhoneNumberProvider')]
    public function testIsValidWithValidPhoneNumbers(string $phoneNumber): void
    {
        $result = $this->validator->isValid($phoneNumber);

        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getMessages());
    }

    public static function validPhoneNumberProvider(): array
    {
        return [
            'UK mobile'          => ['+44.07700123456'],
            'UK landline'        => ['+44.02012345678'],
            'US number'          => ['+1.5551234567'],
            'International long' => ['+123.123456789012345'],
            'No country code'    => ['12345'],
            'Minimum length'     => ['12345'],
            'Maximum length'     => ['123456789012345'],
        ];
    }

    #[DataProvider('invalidPhoneNumberProvider')]
    public function testIsValidWithInvalidPhoneNumbers(string $phoneNumber, string $expectedError): void
    {
        $result = $this->validator->isValid($phoneNumber);

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey($expectedError, $messages);
    }

    public static function invalidPhoneNumberProvider(): array
    {
        return [
            'Too short'                         => ['1234', PhoneNumberValidator::INVALID_NUMBER],
            'Too long'                          => ['1234567890123456', PhoneNumberValidator::INVALID_NUMBER],
            'Invalid country code format'       => ['+44-07700123456', PhoneNumberValidator::INVALID_NUMBER],
            'Letters in number'                 => ['+44.0770abc456', PhoneNumberValidator::INVALID_NUMBER],
            'Spaces in number'                  => ['+44.07700 123456', PhoneNumberValidator::INVALID_NUMBER],
            'Missing digits after country code' => ['+44.', PhoneNumberValidator::INVALID_NUMBER],
            'Empty string'                      => ['', PhoneNumberValidator::INVALID_NUMBER],
        ];
    }

    public function testIsValidWithNonScalarValue(): void
    {
        $result = $this->validator->isValid(['+44.07700123456']);

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(PhoneNumberValidator::NOT_SCALAR, $messages);
        $this->assertEquals('The phone number entered must be a scalar', $messages[PhoneNumberValidator::NOT_SCALAR]);
    }

    public function testIsValidWithNonStringValue(): void
    {
        $result = $this->validator->isValid(1234567890);

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(PhoneNumberValidator::NOT_STRING, $messages);
        $this->assertEquals('The phone number is not a valid string', $messages[PhoneNumberValidator::NOT_STRING]);
    }

    public function testIsValidWithNullValue(): void
    {
        $result = $this->validator->isValid(null);

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(PhoneNumberValidator::NOT_SCALAR, $messages);
    }

    public function testIsValidWithBooleanValue(): void
    {
        $result = $this->validator->isValid(true);

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(PhoneNumberValidator::NOT_STRING, $messages);
    }

    public function testIsValidWithObjectValue(): void
    {
        $result = $this->validator->isValid(new stdClass());

        $this->assertFalse($result);
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(PhoneNumberValidator::NOT_SCALAR, $messages);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        $this->assertEquals('invalidNumber', PhoneNumberValidator::INVALID_NUMBER);
        $this->assertEquals('notScalar', PhoneNumberValidator::NOT_SCALAR);
        $this->assertEquals('notString', PhoneNumberValidator::NOT_STRING);
    }

    public function testValidatorWithOptions(): void
    {
        $validator = new PhoneNumberValidator(['test' => 'option']);

        // Should still work normally with options
        $this->assertTrue($validator->isValid('+44.07700123456'));
        $this->assertFalse($validator->isValid('invalid'));
    }

    public function testRegexPatternBehavior(): void
    {
        // Test edge cases for the regex pattern: /^(\+\d{1,3}\.)?\d{5,15}$/

        // Valid patterns
        $this->assertTrue($this->validator->isValid('+1.12345')); // 1-digit country code (US)
        $this->assertTrue($this->validator->isValid('+12.12345')); // 2-digit country code
        $this->assertTrue($this->validator->isValid('+123.12345')); // 3-digit country code
        $this->assertTrue($this->validator->isValid('12345')); // No country code

        // Invalid patterns
        $this->assertFalse($this->validator->isValid('+1234.12345')); // 4-digit country code
        $this->assertFalse($this->validator->isValid('+12.1234')); // Too few digits after country code
        $this->assertFalse($this->validator->isValid('1234')); // Too few digits without country code
    }

    public function testPregMatchLogicFixed(): void
    {
        // Valid number should return true
        $this->assertTrue($this->validator->isValid('+44.07700123456'));

        // Invalid number should return false
        $this->assertFalse($this->validator->isValid('invalid'));

        // preg_match returns 1 for match, 0 for no match, false for error
        // Now using === 1 for correct validation
    }

    public function testMessageTemplatesAreCorrect(): void
    {
        $this->validator->isValid([123]); // Trigger NOT_SCALAR
        $messages = $this->validator->getMessages();
        $this->assertEquals('The phone number entered must be a scalar', $messages[PhoneNumberValidator::NOT_SCALAR]);

        $this->validator = new PhoneNumberValidator(); // Reset
        $this->validator->isValid(123); // Trigger NOT_STRING
        $messages = $this->validator->getMessages();
        $this->assertEquals('The phone number is not a valid string', $messages[PhoneNumberValidator::NOT_STRING]);

        $this->validator = new PhoneNumberValidator(); // Reset
        $this->validator->isValid('invalid'); // Trigger INVALID_NUMBER
        $messages = $this->validator->getMessages();
        $this->assertEquals('Phone number entered is not valid', $messages[PhoneNumberValidator::INVALID_NUMBER]);
    }

    public function testRealWorldPhoneNumbers(): void
    {
        // These should be valid according to the current pattern
        $validNumbers = [
            '+44.02071234567', // UK London number
            '+1.5551234567', // US number
            '+49.1234567890', // German number
            '07700123456', // UK mobile without country code
        ];

        foreach ($validNumbers as $number) {
            $this->assertTrue($this->validator->isValid($number), "Should be valid: $number");
        }

        // These should be invalid
        $invalidNumbers = [
            '+44 020 7123 4567', // Spaces
            '+44-020-7123-4567', // Hyphens
            '(020) 7123 4567', // Parentheses
            '+44 (0)20 7123 4567', // Mixed format
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertFalse($this->validator->isValid($number), "Should be invalid: $number");
        }
    }
}
