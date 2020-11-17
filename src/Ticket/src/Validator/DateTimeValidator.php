<?php
declare(strict_types=1);

namespace Ticket\Validator;

use Laminas\Validator\AbstractValidator;

class DateTimeValidator extends AbstractValidator
{
    const NOT_STRING = "notString";
    const INVALID_FORMAT = 'invalidForm';

    protected $messageTemplates = [
        self::NOT_STRING => 'Date/Time must be a string',
        self::INVALID_FORMAT => 'Date/Time is not an accepted format',
    ];

    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->error(self::NOT_STRING);
            return false;
        }

        if (\DateTime::createFromFormat('Y-m-d H:i:s', $value) !== false) {
            return true;
        }

        $this->error(self::INVALID_FORMAT);
        return false;
    }
}
