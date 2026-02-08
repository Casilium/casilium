<?php

declare(strict_types=1);

namespace MailService\Service;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

use function array_merge;
use function error_log;
use function http_build_query;
use function sprintf;
use function urlencode;

class MailService
{
    protected array $options;
    protected TemplateRendererInterface $renderer;
    protected ?LoggerInterface $logger = null;
    protected ?MailerInterface $mailer = null;
    protected string $mailFrom;

    public function __construct(TemplateRendererInterface $renderer, array $options, ?LoggerInterface $logger = null)
    {
        $this->renderer = $renderer;
        $this->options  = $options;
        $this->mailFrom = $options['sender'];
        $this->logger   = $logger;
    }

    /**
     * Render email template
     */
    public function prepareBody(string $template, array $options = []): string
    {
        $options = array_merge($options, [
            'layout' => 'layout::email',
        ]);
        if (isset($this->options['app_url'])) {
            $options['app_url'] = $this->options['app_url'];
        }

        return $this->renderer->render($template, $options);
    }

    /**
     * Send email
     */
    public function send(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($this->mailFrom)
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->sendEmail($email);
    }

    /**
     * Send email with attachment
     */
    public function sendWithAttachment(
        string $to,
        string $subject,
        string $body,
        string $attachmentPath,
        ?string $attachmentName = null
    ): void {
        $email = (new Email())
            ->from($this->mailFrom)
            ->to($to)
            ->subject($subject)
            ->html($body)
            ->attachFromPath($attachmentPath, $attachmentName);

        $this->sendEmail($email);
    }

    protected function sendEmail(Email $email): void
    {
        try {
            $this->getMailer()->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logMailerError($e);
            if (($this->options['fail_on_error'] ?? false) === true) {
                throw $e;
            }
        }
    }

    protected function getMailer(): MailerInterface
    {
        if ($this->mailer === null) {
            $dsn          = $this->buildDsn();
            $transport    = Transport::fromDsn($dsn);
            $this->mailer = new Mailer($transport);
        }

        return $this->mailer;
    }

    protected function logMailerError(TransportExceptionInterface $e): void
    {
        $message = sprintf('Mail delivery failed: %s', $e->getMessage());
        if ($this->logger !== null) {
            $this->logger->error($message, ['exception' => $e]);
        } else {
            error_log($message);
        }
    }

    /**
     * Build Symfony Mailer DSN from legacy config format
     */
    protected function buildDsn(): string
    {
        $smtp   = $this->options['smtp_options'] ?? [];
        $config = $smtp['connection_config'] ?? [];

        $host = $smtp['host'] ?? 'localhost';
        $port = $smtp['port'] ?? 25;
        $user = $config['username'] ?? '';
        $pass = $config['password'] ?? '';
        $ssl  = $config['ssl'] ?? null;

        // Determine scheme
        $scheme = 'smtp';
        if ($ssl === 'tls') {
            $scheme = 'smtp';
        } elseif ($ssl === 'ssl') {
            $scheme = 'smtps';
        }

        $query = [];
        if (($config['verify_peer'] ?? true) === false) {
            $query['verify_peer'] = '0';
        }
        if (($config['verify_peer_name'] ?? true) === false) {
            $query['verify_peer_name'] = '0';
        }
        if (($config['allow_self_signed'] ?? false) === true) {
            $query['allow_self_signed'] = '1';
        }

        // Build DSN
        if ($user && $pass) {
            $dsn = sprintf('%s://%s:%s@%s:%d', $scheme, urlencode($user), urlencode($pass), $host, $port);
        } else {
            $dsn = sprintf('%s://%s:%d', $scheme, $host, $port);
        }

        if ($query !== []) {
            $dsn .= '?' . http_build_query($query);
        }

        return $dsn;
    }
}
