<?php

declare(strict_types=1);

namespace SlackIntegration\Service;

use Exception;
use SlackIntegration\Entity\Message;
use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use function str_replace;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;
use const JSON_UNESCAPED_UNICODE;

class Client
{
    /** @var bool */
    protected $enabled = false;

    /** @var string */
    protected $endpoint;

    /** @var string */
    protected $channel;

    /** @var string */
    protected $username;

    /** @var string|null */
    protected $icon;

    /** @var bool */
    protected $linkNames = false;

    /** @var bool */
    protected $unfurlLinks = false;

    /** @var bool  */
    protected $unfurlMedia = true;

    /** @var bool  */
    protected $allowMarkdown = true;

    /** @var array  */
    protected $markDownInAttachments = [];

    public function __construct(array $config)
    {
        $this->enabled = $config['enabled'] ?? false;

        if (! $this->isEnabled()) {
            return;
        }

        $this->endpoint              = $config['endpoint'];
        $this->channel               = $config['channel'] ?? null;
        $this->username              = $config['username'] ?? null;
        $this->icon                  = $config['icon'] ?? null;
        $this->linkNames             = $config['link_names'] ?? false;
        $this->unfurlLinks           = $config['unfurl_links'] ?? false;
        $this->unfurlMedia           = $config['unfurl_media'] ?? true;
        $this->allowMarkdown         = $config['allow_markdown'] ?? true;
        $this->markDownInAttachments = $config['markdown_in_attachments'] ?? [];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): Client
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): Client
    {
        $this->channel = $channel;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Client
    {
        $this->username = $username;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): Client
    {
        $this->icon = $icon;
        return $this;
    }

    public function linkNames(): bool
    {
        return $this->linkNames;
    }

    public function setLinkNames(bool $linkNames): Client
    {
        $this->linkNames = $linkNames;
        return $this;
    }

    public function unfurlLinks(): bool
    {
        return $this->unfurlLinks;
    }

    public function setUnfurlLinks(bool $unfurlLinks): Client
    {
        $this->unfurlLinks = $unfurlLinks;
        return $this;
    }

    public function unfurlMedia(): bool
    {
        return $this->unfurlMedia;
    }

    public function setUnfurlMedia(bool $unfurlMedia): Client
    {
        $this->unfurlMedia = $unfurlMedia;
        return $this;
    }

    public function isAllowedMarkdown(): bool
    {
        return $this->allowMarkdown;
    }

    public function setAllowMarkdown(bool $allowMarkdown): Client
    {
        $this->allowMarkdown = $allowMarkdown;
        return $this;
    }

    public function getMarkDownInAttachments(): array
    {
        return $this->markDownInAttachments;
    }

    public function setMarkDownInAttachments(array $markDownInAttachments): Client
    {
        $this->markDownInAttachments = $markDownInAttachments;
        return $this;
    }

    public function createMessage(): Message
    {
        $message = new Message($this);
        return $message
            ->setChannel($this->getChannel())
            ->setUsername($this->getUsername())
            ->setIcon($this->getIcon())
            ->setAllowMarkdown($this->isAllowedMarkdown())
            ->setMarkdownInAttachments($this->getMarkDownInAttachments());
    }

    public function sendMessage(Message $message): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $payload        = $this->preparePayload($message);
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if (false === $encodedPayload) {
            throw new Exception(sprintf('JSON encoding error %s: %s', json_last_error(), json_last_error_msg()));
        }

        $ch = curl_init($this->getEndpoint());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function preparePayload(Message $message): array
    {
        $payload = [
            'text'         => $this->htmlReplace($message->getText()),
            'channel'      => $message->getChannel(),
            'username'     => $message->getUsername(),
            'link_names'   => $this->linkNames() ? 1 : 0,
            'unfurl_links' => $this->unfurlLinks(),
            'unfurl_media' => $this->unfurlMedia(),
            'mrkdwn'       => $message->isAllowedMarkdown(),
        ];

        if ($icon = $message->getIcon()) {
            $payload[$message->getIconType()] = $icon;
        }

        return $payload;
    }

    private function htmlReplace(string $string): string
    {
        // https://api.slack.com/docs/message-formatting#how_to_escape_characters
        // Also use backslashes to escape double quotes/backslashes themselves,
        // that would otherwise break the JSON.
        // (deal with the slashes before the quotes otherwise the escaped quotes would be re-escaped!)
        return str_replace(['&', '<', '>', '\\', '"'], ['&amp;', '&lt;', '&gt;', '\\\\', '\"'], $string);
    }
}
