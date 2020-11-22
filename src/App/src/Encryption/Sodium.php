<?php
declare(strict_types=1);

namespace App\Encryption;

use App\Exception\SodiumException;

/**
 * Class for using sodium to sensitive data which needs to be stored in a database. The key generated is
 * converted to hex in order to make it readable to store in the configuration files.
 */
class Sodium
{
    /**
     * Use Libodium to encrypt text
     *
     * @param string $plainText text to encrypt
     * @param string $key encryption key
     * @return string encrypted string
     * @throws SodiumException|\SodiumException
     */
    public static function encrypt(string $plainText, string $key): string
    {
        if (! function_exists('sodium_hex2bin')) {
            throw SodiumException::forSodiumNotSupported();
        }

        $key = sodium_hex2bin($key);
        $nonce = self::generateNonce();

        $cipherText = sodium_crypto_secretbox($plainText, $nonce, $key);
        return base64_encode($nonce . $cipherText);
    }

    /**
     * Use Libsodium to decrypt text
     * @param string $cipherText
     * @param string $key
     * @return string
     * @throws SodiumException
     */
    public static function decrypt(string $cipherText, string $key): string
    {
        if (constant('SODIUM_LIBRARY_VERSION') == null) {
            throw SodiumException::forSodiumNotSupported();
        }

        $key = sodium_hex2bin($key);
        $decoded = base64_decode($cipherText, true);
        if ($decoded == false) {
            throw new \Exception('Failed to decode cipher text');
        }

        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipherText = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        $plainText = sodium_crypto_secretbox_open($cipherText, $nonce, $key);
        if ($plainText == false) {
            throw new \Exception('Failed to decode plain text');
        }
        return $plainText;
    }

    /**
     * Generates a key for use with sodium
     *
     * @return string
     * @throws \Exception
     */
    public static function generateKey(): string
    {
        if (constant('SODIUM_LIBRARY_VERSION') == null) {
            throw SodiumException::forSodiumNotSupported();
        }

        $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        return sodium_bin2hex($key);
    }

    /**
     * Generates a nonce for use with sodium
     *
     * @return string
     * @throws \Exception
     */
    public static function generateNonce(): string
    {
        if (constant('SODIUM_LIBRARY_VERSION') == null) {
            throw SodiumException::forSodiumNotSupported();
        }

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return $nonce;
    }
}
