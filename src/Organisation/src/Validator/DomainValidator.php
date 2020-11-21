<?php

declare(strict_types=1);

namespace Organisation\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Hostname;

class DomainValidator extends AbstractValidator
{
    const CANNOT_DECODE_PUNYCODE  = 'hostnameCannotDecodePunycode';
    const INVALID                 = 'hostnameInvalid';
    const INVALID_DASH            = 'hostnameDashCharacter';
    const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    const INVALID_URI             = 'hostnameInvalidUri';
    const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';
    const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    const UNKNOWN_TLD             = 'hostnameUnknownTld';

    // @codingStandardsIgnoreStart
    /**
     * @var array
     */
    protected $messageTemplates = [
        self::CANNOT_DECODE_PUNYCODE  => "%value% : The input appears to be a DNS hostname but the given punycode notation cannot be decoded",
        self::INVALID                 => "%value% : Invalid type given. String expected",
        self::INVALID_DASH            => "%value% : The input appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME        => "%value% : The input does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "%value% : The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME      => "%value% : The input does not appear to be a valid local network name",
        self::INVALID_URI             => "%value% : The input does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED  => "%value% : The input appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED  => "%value% : The input appears to be a local network name but local network names are not allowed",
        self::UNDECIPHERABLE_TLD      => "%value% : The input appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD             => "%value%: The input appears to be a DNS hostname but cannot match TLD against known list",
    ];
    // @codingStandardsIgnoreEnd

    public function isValid($value)
    {
        $validator = new Hostname();
        foreach ($value as $domain) {
            $this->messageTemplates = $validator->getMessageTemplates();

            if (!$validator->isValid($domain)) {
                $errorMsg = $validator->getMessages();
                $errorMsgKey = key($errorMsg);
                $this->error($errorMsgKey, $domain);
                return false;
            }
        }

        return true;

    }
}