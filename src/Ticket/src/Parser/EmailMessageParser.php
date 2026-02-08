<?php

declare(strict_types=1);

namespace Ticket\Parser;

use function filter_var;
use function htmlspecialchars;
use function preg_match_all;
use function preg_replace;
use function strip_tags;
use function strrpos;
use function substr;

use const ENT_QUOTES;
use const FILTER_VALIDATE_EMAIL;

class EmailMessageParser
{
    protected const MAIL_SEPARATOR = '--- REPLY ABOVE THIS LINE---';

    /**
     * Sanitise email body content
     */
    public static function sanitiseBody(string $body): string
    {
        $body = strip_tags($body);
        $body = self::stripImages($body);
        $body = self::sanitiseLineBreaks($body);
        $body = self::stripSignature($body);

        return htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Extract e-mail address from recipient string
     * e.g. "Test User" <test@example.com> returns "test@example.com"
     */
    public static function getEmail(string $string): ?string
    {
        $pattern = '/<(.*?)>/i';

        preg_match_all($pattern, $string, $matches);

        $email = $matches[1][0] ?? $string;
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return $email;
        }
        return null;
    }

    /**
     * Strip multiple new lines
     */
    public static function sanitiseLineBreaks(string $string): string
    {
        return preg_replace("/[\r\n]+\s+/", "\r", $string) ?? $string;
    }

    /**
     * Strip signature separator and everything after
     */
    public static function stripSignature(string $body): string
    {
        if (strrpos($body, self::MAIL_SEPARATOR) !== false) {
            $body = substr($body, 0, strrpos($body, self::MAIL_SEPARATOR));
        }

        return $body;
    }

    /**
     * Strip embedded images from email
     */
    public static function stripImages(string $body): string
    {
        $body = preg_replace('/<img\b[^>]*\bsrc\s*=\s*[\'"]cid[^>]*>/im', '', $body) ?? $body;
        $body = preg_replace('/\[cid:.*]/im', '', $body) ?? $body;
        return $body;
    }
}
