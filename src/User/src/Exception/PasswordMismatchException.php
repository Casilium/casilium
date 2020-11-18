<?php

declare(strict_types=1);

namespace User\Exception;

use RuntimeException;
use function sprintf;

class PasswordMismatchException extends RuntimeException implements ExceptionInterface
{
    public static function whenVerifying(): self
    {
        return new self(sprintf(
            'Current password does not match'
        ));
    }

    public static function whenPasswordsAreSame(): self
    {
        return new self(sprintf(
            'Current password cannot be same as new password'
        ));
    }
}
