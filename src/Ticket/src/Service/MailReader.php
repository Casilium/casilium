<?php

declare(strict_types=1);

namespace Ticket\Service;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MessageInterface;
use Ddeboer\Imap\Search\Flag\Unseen;
use Ddeboer\Imap\Server;
use Exception;
use Ticket\Parser\EmailMessageParser;

use function htmlspecialchars;
use function nl2br;
use function sprintf;

use const ENT_QUOTES;

class MailReader
{
    private ?ConnectionInterface $connection = null;
    private string $host;
    private string $user;
    private string $password;
    private bool $ssl;

    public function __construct(string $host, string $user, string $password, bool $ssl = true)
    {
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->ssl      = $ssl;
    }

    public function getConnection(): ConnectionInterface
    {
        if ($this->connection === null) {
            try {
                $flags            = $this->ssl ? '/imap/ssl/validate-cert' : '/imap/notls';
                $server           = new Server($this->host, '993', $flags);
                $this->connection = $server->authenticate($this->user, $this->password);
            } catch (Exception $ex) {
                throw new Exception(
                    sprintf('Unable to connect to mail server %s: %s', $this->host, $ex->getMessage()),
                    0,
                    $ex
                );
            }
        }

        return $this->connection;
    }

    /**
     * Process unread messages from INBOX
     *
     * @return array<string, array> Messages keyed by unique ID
     */
    public function processMessages(): array
    {
        $messages   = [];
        $connection = $this->getConnection();
        $mailbox    = $connection->getMailbox('INBOX');

        // Search for unseen messages only
        $unseenMessages = $mailbox->getMessages(new Unseen());

        $cnt = 0;
        foreach ($unseenMessages as $message) {
            $cnt++;

            // Keep connection alive for large mailboxes
            if ($cnt % 5 === 0) {
                $connection->ping();
            }

            $messageUniqueId = (string) $message->getNumber();

            try {
                $from = $message->getFrom();
                $to   = $message->getTo();

                // Skip messages without valid from/to
                if ($from === null || empty($to)) {
                    continue;
                }

                $fromEmail = $from->getAddress();
                $toEmail   = $to[0]->getAddress();

                $email = [
                    'from'      => $fromEmail,
                    'to'        => $toEmail,
                    'date'      => $message->getDate()?->format('r') ?? '',
                    'subject'   => $message->getSubject() ?? '(No Subject)',
                    'flags'     => [],
                    'messageid' => $message->getNumber(),
                    'body'      => EmailMessageParser::sanitiseBody($this->getMessageBody($message)),
                ];

                if ($email['from'] !== null && $email['to'] !== null) {
                    $messages[$messageUniqueId] = $email;

                    // Mark message as seen after successful processing
                    $message->markAsSeen();
                }
            } catch (Exception $e) {
                // Log error but continue processing other messages
                continue;
            }
        }

        $connection->close();
        return $messages;
    }

    /**
     * Extract message body (prefer HTML, fall back to plain text)
     */
    protected function getMessageBody(MessageInterface $message): string
    {
        $html = $message->getBodyHtml();
        if ($html !== null) {
            return $html;
        }

        $text = $message->getBodyText();
        if ($text !== null) {
            // Convert plain text to simple HTML
            return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
        }

        return '';
    }
}
