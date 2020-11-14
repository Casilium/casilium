<?php

declare(strict_types=1);

namespace Organisation\Exception;

class OrganisationSitesExistException extends \RuntimeException implements ExceptionInterface
{
    public static function whenDeleting(string $name): self
    {
        return new self(sprintf(
            'Cannot delete organisation "%s" while it still has sites',
            $name
        ));
    }
}