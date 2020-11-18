<?php

declare(strict_types=1);

namespace Organisation\Validator;

use Laminas\Validator\AbstractValidator;
use function preg_match;

/**
 * Class OrganisationNameValidator - does what it says on the tin
 */
class OrganisationNameValidator extends AbstractValidator
{
    // validation failure message IDs
    protected const INVALID_CHARACTERS = 'invalidCharacters';

    // validation failure messages
    protected $messageTemplates = [
        self::INVALID_CHARACTERS => 'The name contains invalid characters',
    ];

    /**
     * Validates an organisation name
     *
     * @param string $value
     */
    public function isValid($value): bool
    {
        $this->setValue($value);

        $pattern = "/^[a-z0-9\s]+$/i";
        if (preg_match($pattern, $value)) {
            return true;
        }

        $this->error(self::INVALID_CHARACTERS);
        return false;
    }
}
