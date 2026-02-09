<?php

declare(strict_types=1);

namespace TicketTest\Service;

use Ddeboer\Imap\MessageInterface;
use PHPUnit\Framework\TestCase;
use Ticket\Service\MailReader;

final class MailReaderTest extends TestCase
{
    private function createReader(): MailReader
    {
        return new class ('imap.example.com', 'user', 'pass', false) extends MailReader {
            public function exposeBody(MessageInterface $message): string
            {
                return $this->getMessageBody($message);
            }
        };
    }

    public function testGetMessageBodyPrefersHtml(): void
    {
        $reader  = $this->createReader();
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->once())
            ->method('getBodyHtml')
            ->willReturn('<p>Hello</p>');

        $message->expects($this->never())->method('getBodyText');

        $body = $reader->exposeBody($message);

        $this->assertSame('<p>Hello</p>', $body);
    }

    public function testPlainTextBodyGetsConvertedToHtml(): void
    {
        $reader  = $this->createReader();
        $message = $this->createMock(MessageInterface::class);
        $message->method('getBodyHtml')->willReturn(null);
        $message->method('getBodyText')->willReturn("Line 1\nLine 2");

        $body = $reader->exposeBody($message);

        $this->assertSame("Line 1<br />\nLine 2", $body);
    }

    public function testEmptyBodyReturnsEmptyString(): void
    {
        $reader  = $this->createReader();
        $message = $this->createMock(MessageInterface::class);
        $message->method('getBodyHtml')->willReturn(null);
        $message->method('getBodyText')->willReturn(null);

        $body = $reader->exposeBody($message);

        $this->assertSame('', $body);
    }
}
