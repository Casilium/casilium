<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\CannedResponse;
use Ticket\Handler\ListCannedResponseHandler;
use Ticket\Service\CannedResponseManager;

class ListCannedResponseHandlerTest extends TestCase
{
    public function testRendersCannedResponses(): void
    {
        $manager   = $this->createMock(CannedResponseManager::class);
        $renderer  = $this->createMock(TemplateRendererInterface::class);
        $responses = [new CannedResponse(), new CannedResponse()];

        $manager->expects($this->once())->method('findAll')->willReturn($responses);
        $renderer->expects($this->once())
            ->method('render')
            ->with('ticket::list-canned-response', ['cannedResponses' => $responses])
            ->willReturn('<html>Canned responses</html>');

        $handler  = new ListCannedResponseHandler($manager, $renderer);
        $response = $handler->handle(new ServerRequest());

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('<html>Canned responses</html>', (string) $response->getBody());
    }
}
