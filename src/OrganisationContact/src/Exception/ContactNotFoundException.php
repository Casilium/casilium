<?php

declare(strict_types=1);

namespace OrganisationContact\Exception;

class ContactNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public static function whenSearchingById(int $id): self
    {
        return new self(sprintf(
            'An contact could with the id "%s" could not be found',
            $id
        ));
    }
}