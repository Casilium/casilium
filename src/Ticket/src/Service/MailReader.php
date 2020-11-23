<?php
declare(strict_types=1);

namespace Ticket\Service;

use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Imap;
use Ticket\Parser\EmailMessageParser;

class MailReader
{
    /** @var Imap */
    private $connection;

    /** @var string */
    private $host;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var bool  */
    private $ssl;

    public function __construct(string $host, string $user, string $password, bool $ssl = true)
    {
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->ssl      = $ssl;
    }

    /**
     * @return Imap
     */
    public function getConnection(): Imap
    {
        if ($this->connection == null) {
            $this->connection = new Imap([
                'host'     => $this->host,
                'user'     => $this->user,
                'password' => $this->password,
                'ssl'      => true,
            ]);
        }

        return $this->connection;
    }

    public function processMessages(): array
    {
        $data = [
            'messages'  => $this->getConnection()->countMessages(),
            'unread'    => $this->getConnection()->countMessages(\Laminas\Mail\Storage::FLAG_UNSEEN),
            'read'      => $this->getConnection()->countMessages(\Laminas\Mail\Storage::FLAG_SEEN),
        ];

        $uniqueIds = [];
        $messages  = [];

        $cnt = 0;
        foreach ($this->getConnection() as $index => $message) {
            $cnt++;
            if ($cnt % 5 === 0) {
                // keep alive
                $this->getConnection()->noop();
            }

            // ignore read mail
            if ($message->hasFlag(Storage::FLAG_SEEN)) {
                continue;
            }

            $messageUniqueId = $this->getConnection()->getUniqueId($index);
            if (is_array($messageUniqueId)) {
                throw new \Exception('Expected single result received array');
            }

            $uniqueIds[] = $messageUniqueId;
            $messages[$messageUniqueId] = [
                'from'      => EmailMessageParser::getEmail($message->from),
                'to'        => EmailMessageParser::getEmail($message->to),
                'date'      => $message->date,
                'subject'   => $message->subject,
                'flags'     => $message->getFlags(),
                'messageid' => $this->getConnection()->getNumberByUniqueId($messageUniqueId),
                'body'      => EmailMessageParser::getMessageBody($message),
            ];
        }

        $this->connection->close();
        return $messages;

        /*
        $cnt = 0;
        foreach ($uniqueIds as $uniqueId) {
            $cnt++;
            if ($cnt % 5 == 0) {
                // keep alive
                $this->getConnection()->noop();
            }

            $messageId = $this->getConnection()->getNumberByUniqueId($uniqueId);
            $this->getConnection()->moveMessage($messageId, 'Trash'); // this ID shifts all the time?!?
        }


        $this->getConnection()->close();
        */
    }
}
