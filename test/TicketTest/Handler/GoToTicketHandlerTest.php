<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\Ticket;
use Ticket\Handler\GoToTicketHandler;
use Ticket\Service\TicketService;

class GoToTicketHandlerTest extends TestCase
{
    private GoToTicketHandler $handler;
    private TicketService $ticketService;
    private TemplateRendererInterface $renderer;
    private UrlHelper $urlHelper;

    protected function setUp(): void
    {
        $this->ticketService = $this->createMock(TicketService::class);
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
        $this->urlHelper = $this->createMock(UrlHelper::class);
        
        $this->handler = new GoToTicketHandler(
            $this->ticketService,
            $this->renderer,
            $this->urlHelper
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new GoToTicketHandler(
            $this->ticketService,
            $this->renderer,
            $this->urlHelper
        );
        
        $this->assertInstanceOf(GoToTicketHandler::class, $handler);
    }

    public function testHandleWithValidTicketIdRedirectsToTicketView(): void
    {
        $ticketId = 123;
        $ticketUuid = 'ticket-uuid-abc123';
        $ticket = $this->createMockTicket($ticketUuid);
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => (string) $ticketId]);
        
        $this->ticketService->expects($this->once())
            ->method('findTicketById')
            ->with($ticketId)
            ->willReturn($ticket);
        
        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('ticket.view', ['ticket_id' => $ticketUuid])
            ->willReturn('/tickets/view/ticket-uuid-abc123');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/tickets/view/ticket-uuid-abc123', $response->getHeaderLine('Location'));
    }

    public function testHandleWithEmptyTicketIdReturns404(): void
    {
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => '']); // Empty ticket_id parameter
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>404 Not Found</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('<html>404 Not Found</html>', $response->getBody()->getContents());
    }

    public function testHandleWithNoQueryParamsReturns404(): void
    {
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams([]); // No parameters at all
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>404 Not Found</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandleWithZeroTicketIdReturns404(): void
    {
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => '0']);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>404 Not Found</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandleWithNonNumericTicketIdReturns404(): void
    {
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => 'invalid']);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>404 Not Found</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandleWithNonExistentTicketReturns404(): void
    {
        $ticketId = 999;
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => (string) $ticketId]);
        
        $this->ticketService->expects($this->once())
            ->method('findTicketById')
            ->with($ticketId)
            ->willReturn(null); // Ticket not found
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>Ticket Not Found</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('<html>Ticket Not Found</html>', $response->getBody()->getContents());
    }

    public function testHandleConvertsStringTicketIdToInteger(): void
    {
        $ticketId = 456;
        $ticketUuid = 'ticket-uuid-def456';
        $ticket = $this->createMockTicket($ticketUuid);
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => '456']); // String value
        
        $this->ticketService->expects($this->once())
            ->method('findTicketById')
            ->with($ticketId) // Should be converted to integer
            ->willReturn($ticket);
        
        $this->urlHelper->method('generate')->willReturn('/tickets/view/ticket-uuid-def456');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testHandleUsesTicketUuidInRedirectUrl(): void
    {
        $ticketId = 789;
        $ticketUuid = 'specific-uuid-ghi789';
        $ticket = $this->createMockTicket($ticketUuid);
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => (string) $ticketId]);
        
        $this->ticketService->method('findTicketById')->willReturn($ticket);
        
        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('ticket.view', ['ticket_id' => $ticketUuid])
            ->willReturn('/tickets/view/specific-uuid-ghi789');
        
        $response = $this->handler->handle($request);
        
        $this->assertEquals('/tickets/view/specific-uuid-ghi789', $response->getHeaderLine('Location'));
    }

    public function testHandleCallsCorrectServiceMethod(): void
    {
        $ticketId = 321;
        $ticket = $this->createMockTicket('test-uuid');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => (string) $ticketId]);
        
        $this->ticketService->expects($this->once())
            ->method('findTicketById')
            ->with($ticketId)
            ->willReturn($ticket);
        
        $this->urlHelper->method('generate')->willReturn('/test');
        
        $this->handler->handle($request);
    }

    public function testHandleCallsCorrectUrlHelperMethod(): void
    {
        $ticketId = 654;
        $ticketUuid = 'url-helper-uuid';
        $ticket = $this->createMockTicket($ticketUuid);
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => (string) $ticketId]);
        
        $this->ticketService->method('findTicketById')->willReturn($ticket);
        
        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('ticket.view', ['ticket_id' => $ticketUuid])
            ->willReturn('/generated-url');
        
        $this->handler->handle($request);
    }

    public function testHandleWithFloatTicketIdConvertsToInteger(): void
    {
        $ticketId = 123;
        $ticket = $this->createMockTicket('float-test-uuid');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => '123.45']); // Float string
        
        $this->ticketService->expects($this->once())
            ->method('findTicketById')
            ->with($ticketId) // Should be converted to integer 123
            ->willReturn($ticket);
        
        $this->urlHelper->method('generate')->willReturn('/test');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testHandleRendersCorrect404Template(): void
    {
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['ticket_id' => 'invalid']);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('error::404')
            ->willReturn('<html>404 Template</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertEquals('<html>404 Template</html>', $response->getBody()->getContents());
    }

    private function createMockTicket(string $uuid): Ticket
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('getUuid')->willReturn($uuid);
        return $ticket;
    }
}