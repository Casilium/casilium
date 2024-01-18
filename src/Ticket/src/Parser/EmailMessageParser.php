<?php

declare(strict_types=1);

namespace Ticket\Parser;

use Exception;
use Laminas\Mail\Storage\Message;
use RecursiveIteratorIterator;

use function base64_decode;
use function filter_var;
use function gettype;
use function is_string;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strcasecmp;
use function strip_tags;
use function strrpos;
use function strtok;
use function substr;

use const FILTER_SANITIZE_STRING;
use const FILTER_VALIDATE_EMAIL;

class EmailMessageParser
{
    // Body Part Types
    protected const PLAINTEXT      = 'text/plain';
    protected const HTML           = 'text/html';
    protected const MAIL_SEPARATOR = '--- REPLY ABOVE THIS LINE---';

    /**
     * Attempt to get message body, plain text first followed by HTML
     *
     * @param Message $message email message
     * @return string|null message body
     */
    public static function getMessageBody(Message $message): ?string
    {
        $body = self::getMessagePart($message, self::PLAINTEXT);
        if ($body === null) {
            $body = self::getMessagePart($message, self::HTML);
        }
        return $body;
    }

    /**
     * Extract mime part from mail message
     *
     * @param Message $message Email message
     * @param string $type part to get
     * @return string|null part
     */
    public static function getMessagePart(Message $message, string $type = self::PLAINTEXT): ?string
    {
        $body = null;

        /** @var Message $part */
        foreach (new RecursiveIteratorIterator($message) as $part) {
            try {
                $contentType = $part->getHeaderField('content-type');
                if (! is_string($contentType)) {
                    throw new Exception(sprintf(
                        'Expected string, received %s',
                        gettype($contentType)
                    ));
                }
                if (strcasecmp(strtok($contentType, ';'), $type) === 0) {
                    if (self::isBase64Encoded($part)) {
                        $body = base64_decode($part->getContent(), true);
                    } else {
                        $body = $part->getContent();
                    }
                    break;
                }
            } catch (Exception $e) {
                // ignore
            }
        }
        if ($body !== null) {
            $body = strip_tags($body);
            $body = self::stripImages($body);
            $body = self::sanitiseLineBreaks($body);
            $body = self::stripSignature($body);

            return filter_var($body, FILTER_SANITIZE_STRING);
        }

        return null;
    }

    /**
     * Checks if a message is base64 encoded, for some reason Microsoft Outlook seems to send plain text e-mails
     * as base64 encoded, which will need to be decoded.
     *
     * @param Message $part message part
     * @return bool whether is base64 encoded or not
     */
    public static function isBase64Encoded(Message $part)
    {
        $encoding = $part->getHeaderField('ContentTransferEncoding') ?? null;
        if (is_string($encoding) && strcasecmp($encoding, 'base64') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Extract e-mail address from recipient string (ie "Test User" <test@example.com) will return
     * "test@example.com"
     *
     * @param string $string to extract from
     * @return string|null extracted e-mail address or null
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
     *
     * @param string $string string to parse
     * @return string parsed string
     */
    public static function sanitiseLineBreaks(string $string): string
    {
        return preg_replace("/[\r\n]+\s+/", "\r", $string);
    }

    /**
     * Strip signature
     *
     * @param string $body string to parse
     * @return string|string[]|null
     */
    public static function stripSignature(string $body): string
    {
        if (strrpos($body, self::MAIL_SEPARATOR) !== false) {
            $body = substr($body, 0, strrpos($body, self::MAIL_SEPARATOR));
        }

        //$body = preg_replace('/\s*(.+)\s*[\r\n]--\s+.*/s', '$1', $body);
        return $body;
    }

    /**
     * Strip images from email
     *
     * @param string $body string to parse
     * @return string parsed string
     */
    public static function stripImages(string $body): string
    {
        $body = preg_replace('/<img\b[^>]*\bsrc\s*=\s*[\'"]cid[^>]*>/im', '', $body);
        $body = preg_replace('/\[cid:.*]/im', '', $body);
        return $body;
    }
}
