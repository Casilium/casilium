<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class SodiumException extends Exception
{
    public static function forSodiumNotSupported(): self
    {
        return new self('Libsodium is not supported');
    }

    public static function forEncryptionKeyNotFoundInConfig(): self
    {
        return new self('Sodium encryption key was not found in configuration');
    }
}
