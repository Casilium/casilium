<?php
declare(strict_types=1);

namespace User\Exception;

use Throwable;

class UnauthorizedException extends \Exception
{
    /**
     * @param string|null $resource
     * @param int $code
     * @param Throwable|null $previous
     * @return UnauthorizedException
     */
    public static function forForbidden(string $resource = null, $code = 401, Throwable $previous = null): self
    {
        $message = 'Access to the requested resource is forbidden';

        if ($resource !== null) {
            $message = sprintf(
                'Access to the requested resource (%s) is forbidden',
                $resource
            );
        }

        return new static ($message, $code, $previous);
    }
}
