<?php

declare(strict_types=1);

namespace User\Exception;

use Exception;
use Throwable;

use function sprintf;

class UnauthorizedException extends Exception
{
    /**
     * @param int $code
     * @return UnauthorizedException
     */
    public static function forForbidden(?string $resource = null, $code = 401, ?Throwable $previous = null): self
    {
        $message = 'Access to the requested resource is forbidden';

        if ($resource !== null) {
            $message = sprintf(
                'Access to the requested resource (%s) is forbidden',
                $resource
            );
        }

        return new static($message, $code, $previous);
    }
}
