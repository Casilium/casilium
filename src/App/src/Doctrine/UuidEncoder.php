<?php

declare(strict_types=1);

namespace App\Doctrine;

use Ramsey\Uuid\UuidInterface;
use Throwable;

use function array_reduce;
use function str_pad;
use function str_replace;
use function substr_replace;

use const STR_PAD_LEFT;

/**
 * Class UuidEncoder used for encoding uuid to make user friendly URL
 *
 * @see https://medium.com/@galopintitouan/auto-increment-is-the-devil-using-uuids-in-symfony-and-doctrine-71763721b9a9
 */
class UuidEncoder
{
    /**
     * Encode uuid to friendly
     */
    public function encode(UuidInterface $uuid): string
    {
        return gmp_strval(
            gmp_init(
                str_replace('-', '', $uuid->toString()),
                16
            ),
            62
        );
    }

    /**
     * Decode from friendly to uuid
     */
    public function decode(string $encoded): ?UuidInterface
    {
        try {
            return Uuid::fromString(array_reduce(
                [20, 16, 12, 8],
                function ($uuid, $offset) {
                    return substr_replace($uuid, '-', $offset, 0);
                },
                str_pad(
                    gmp_strval(
                        gmp_init($encoded, 62),
                        16
                    ),
                    32,
                    '0',
                    STR_PAD_LEFT
                )
            ));
        } catch (Throwable $e) {
            return null;
        }
    }
}
