<?php

declare(strict_types=1);

namespace SlackIntegration\Entity;

use SlackIntegration\Service\Client;

use function mb_strlen;
use function mb_substr;

class Message
{
    public const ICON_TYPE_URL   = 'icon_url';
    public const ICON_TYPE_EMOJI = 'icon_emoji';

    /** @var Client */
    protected $client;

    /** @var string */
    protected $username;

    /** @var string */
    protected $text;

    /** @var string */
    protected $channel;

    /** @var string */
    protected $icon;

    /** @var string */
    protected $iconType;

    /** @var bool */
    protected $allowMarkdown = true;

    /** @var array */
    protected $markDownInAttachments = [];

    /** @var array */
    protected $attachments = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Message
    {
        $this->text = $text;
        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): Message
    {
        $this->channel = $channel;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Message
    {
        $this->username = $username;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon = null): Message
    {
        if (null === $icon) {
            $this->iconType = null;

            return $this;
        }

        // set icon type depending on string passed
        if (mb_substr($icon, 0, 1) === ':' && mb_substr($icon, mb_strlen($icon) - 1, 1) === ':') {
            $this->iconType = self::ICON_TYPE_EMOJI;
        } else {
            $this->iconType = self::ICON_TYPE_URL;
        }

        $this->icon = $icon;

        return $this;
    }

    public function getIconType(): string
    {
        return $this->iconType;
    }

    public function isAllowedMarkdown(): bool
    {
        return $this->allowMarkdown;
    }

    public function setAllowMarkdown(bool $flag): Message
    {
        $this->allowMarkdown = $flag;
        return $this;
    }

    public function enableMarkdown(): Message
    {
        $this->allowMarkdown = true;
        return $this;
    }

    public function disableMarkDown(): Message
    {
        $this->allowMarkdown = false;
        return $this;
    }

    public function getMarkdownInAttachments(): array
    {
        return $this->markDownInAttachments;
    }

    public function setMarkdownInAttachments(array $fields): Message
    {
        $this->markDownInAttachments = $fields;
        return $this;
    }

    public function from(string $username): Message
    {
        $this->username = $username;
        return $this;
    }

    public function to(string $channel): Message
    {
        $this->channel = $channel;
        return $this;
    }

    public function withIcon(string $icon): Message
    {
        $this->setIcon($icon);
        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): Message
    {
        $this->clearAttachments();

        return $this;
    }

    public function clearAttachments(): Message
    {
        $this->attachments = [];
        return $this;
    }

    public function send(?string $text = null): Message
    {
        if (null !== $text) {
            $this->setText($text);
        }

        $this->client->sendMessage($this);
        return $this;
    }
}
