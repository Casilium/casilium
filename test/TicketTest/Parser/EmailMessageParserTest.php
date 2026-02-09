<?php

declare(strict_types=1);

namespace TicketTest\Parser;

use PHPUnit\Framework\TestCase;
use Ticket\Parser\EmailMessageParser;

use function trim;

final class EmailMessageParserTest extends TestCase
{
    public function testSanitiseBodyStripsStyleBlocks(): void
    {
        $html = <<<HTML
            <style>P {margin-top:0;margin-bottom:0;}</style>
            <p>Hello</p>
        HTML;

        $result = EmailMessageParser::sanitiseBody($html);

        $this->assertSame('Hello', trim($result));
    }
}
