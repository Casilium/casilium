<?php

declare(strict_types=1);

namespace Organisation\Exception;

class OrganisationExistsException extends \RuntimeException implements ExceptionInterface
{
    public static function whenCreating(string $name): self
    {
        return new self(sprintf(
            'An organisation already exists with the name "%s" already exists',
            $name
        ));
    }
}