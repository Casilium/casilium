<?php

declare(strict_types=1);

namespace Mfa\Service;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

use function base64_encode;
use function bindec;
use function chr;
use function decbin;
use function floor;
use function hash_equals;
use function hash_hmac;
use function ord;
use function pack;
use function random_bytes;
use function rawurlencode;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function str_pad;
use function str_split;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;
use function time;
use function trim;
use function unpack;

use const E_DEPRECATED;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * Lightweight TOTP helper so we can drop sonata-project/google-authenticator.
 *
 * Implements RFC 6238 using Base32 secrets and SHA1 HMAC.
 */
class TotpService
{
    private const string BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private int $digits;
    private int $period;
    private int $window;
    private string $algorithm;

    public function __construct(
        int $digits = 6,
        int $period = 30,
        int $window = 1,
        string $algorithm = 'sha1'
    ) {
        $this->digits    = $digits;
        $this->period    = $period;
        $this->window    = $window;
        $this->algorithm = $algorithm;
    }

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function verifyCode(string $secret, string $code, ?int $timestamp = null): bool
    {
        $timestamp ??= time();
        $timeSlice   = (int) floor($timestamp / $this->period);

        for ($offset = -$this->window; $offset <= $this->window; $offset++) {
            $calculated = $this->calculateCode($secret, $timeSlice + $offset);
            if (hash_equals($calculated, trim($code))) {
                return true;
            }
        }

        return false;
    }

    public function getProvisioningUri(string $email, string $secret, string $issuer): string
    {
        $label = rawurlencode($this->buildLabel($issuer, $email));

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s',
            $label,
            rawurlencode($secret),
            rawurlencode($issuer)
        );
    }

    public function getQrCodeUrl(string $email, string $secret, string $issuer, int $size = 200): string
    {
        $otpauth = $this->getProvisioningUri($email, $secret, $issuer);

        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        set_error_handler(static function ($severity) {
            return $severity === E_DEPRECATED;
        });

        try {
            $svg = $writer->writeString($otpauth);
        } finally {
            restore_error_handler();
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function buildLabel(string $issuer, string $email): string
    {
        if ($issuer !== '') {
            return sprintf('%s:%s', $issuer, $email);
        }

        return $email;
    }

    private function calculateCode(string $secret, int $timeSlice): string
    {
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac($this->algorithm, $time, $this->base32Decode($secret), true);

        $offset        = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value         = unpack('N', $truncatedHash)[1];
        $value        &= 0x7FFFFFFF;
        $modulo        = 10 ** $this->digits;

        return str_pad((string) ($value % $modulo), $this->digits, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $alphabet   = self::BASE32_ALPHABET;
        $binaryData = '';
        foreach (str_split($bytes) as $char) {
            $binaryData .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        $chunks  = str_split($binaryData, 5);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }

            $encoded .= $alphabet[bindec($chunk)];
        }

        return strtoupper($encoded);
    }

    private function base32Decode(string $encoded): string
    {
        $cleaned  = strtoupper($encoded);
        $alphabet = self::BASE32_ALPHABET;
        $binary   = '';

        foreach (str_split($cleaned) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) {
                throw new RuntimeException('Invalid Base32 character encountered');
            }

            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $bytes    = '';
        $segments = str_split($binary, 8);
        foreach ($segments as $segment) {
            if (strlen($segment) === 8) {
                $bytes .= chr(bindec($segment));
            }
        }

        return $bytes;
    }
}
