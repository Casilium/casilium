<?php

declare(strict_types=1);

namespace TicketTest\Entity;

use PHPUnit\Framework\TestCase;
use Ticket\Entity\CannedResponse;

class CannedResponseTest extends TestCase
{
    public function testStoresTitleAndResponse(): void
    {
        $cannedResponse = new CannedResponse();
        $cannedResponse->setId(12);
        $cannedResponse->setTitle('Password reset');
        $cannedResponse->setResponse("Please reset your password.\n\nLet us know if you need help.");

        $this->assertSame(12, $cannedResponse->getId());
        $this->assertSame('Password reset', $cannedResponse->getTitle());
        $this->assertSame(
            "Please reset your password.\n\nLet us know if you need help.",
            $cannedResponse->getResponse()
        );
    }
}
