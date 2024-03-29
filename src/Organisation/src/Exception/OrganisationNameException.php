<?php

declare(strict_types=1);

namespace Organisation\Exception;

use RuntimeException;

use function sprintf;

class OrganisationNameException extends RuntimeException implements ExceptionInterface
{
    public static function whenCreating(string $name): self
    {
        return new self(sprintf(
            'Organisation name "%s" contains invalid characters',
            $name
        ));
    }
}
