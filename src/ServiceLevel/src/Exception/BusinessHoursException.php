<?php
declare(strict_types=1);

namespace ServiceLevel\Exception;

use Exception;

class BusinessHoursException extends Exception
{
    public static function forInvalidDate(): self
    {
        return new self('Date specified is invalid');
    }
}
