<?php

declare(strict_types=1);

namespace Ticket\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

class MailConnectionException extends RuntimeException
{
    public static function forQueueConnectionFailure(
        string $queueName,
        string $host,
        ?Throwable $previous = null
    ): self {
        return new self(
            sprintf('Failed to connect to mail server for queue "%s" (host: %s)', $queueName, $host),
            0,
            $previous
        );
    }

    public static function forMessageProcessingFailure(
        string $queueName,
        string $messageId,
        ?Throwable $previous = null
    ): self {
        return new self(
            sprintf('Failed to process message "%s" in queue "%s"', $messageId, $queueName),
            0,
            $previous
        );
    }
}
