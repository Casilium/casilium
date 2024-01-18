<?php

declare(strict_types=1);

namespace Ticket\Validator;

use DateTime;
use Laminas\Validator\AbstractValidator;

use function is_string;

class DateTimeValidator extends AbstractValidator
{
    public const NOT_STRING     = "notString";
    public const INVALID_FORMAT = 'invalidForm';

    protected array $messageTemplates = [
        self::NOT_STRING     => 'Date/Time must be a string',
        self::INVALID_FORMAT => 'Date/Time is not an accepted format',
    ];

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            $this->error(self::NOT_STRING);
            return false;
        }

        if (DateTime::createFromFormat('Y-m-d H:i:s', $value) !== false) {
            return true;
        }

        $this->error(self::INVALID_FORMAT);
        return false;
    }
}
