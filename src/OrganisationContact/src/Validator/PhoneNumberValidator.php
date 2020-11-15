<?php
declare(strict_types=1);

namespace OrganisationContact\Validator;

use Laminas\Validator\AbstractValidator;

class PhoneNumberValidator extends AbstractValidator
{
    // validation failure message IDs
    public const INVALID_NUMBER = 'invalidNumber';
    public const NOT_SCALAR     = 'notScalar';
    public const NOT_STRING     = 'notString';

    /**
     * Validation failure messages
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_NUMBER => 'Phone number entered is not valid',
        self::NOT_SCALAR     => 'The phone number entered must be a scalar',
        self::NOT_STRING     => 'The phone number is not a valid string',
    ];

    /**
     * PhoneNumberValidator constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * Check if phone number is valid
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (! is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false;
        }

        if (! is_string($value)) {
            $this->error(self::NOT_STRING);
            return false;
        }

        $pattern = '/^(\+\d{2,3}\.)?\d{5,15}$/';
        if (preg_match($pattern, $value) !== false) {
            return true;
        }

        $this->error(self::INVALID_NUMBER);
        return false;
    }
}
