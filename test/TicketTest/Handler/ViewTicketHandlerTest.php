<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use OrganisationContact\Entity\Contact;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;
use Ticket\Form\TicketResponseForm;
use Ticket\Handler\ViewTicketHandler;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;
use UserAuthentication\Entity\IdentityInterface;

class ViewTicketHandlerTest extends TestCase
{
    private ViewTicketHandler $handler;
    private TicketService $ticketService;
    private TicketHydrator $hydrator;
    private TemplateRendererInterface $renderer;
    private UrlHelper $urlHelper;

    protected function setUp(): void
    {
        $this->ticketService = $this->createMock(TicketService::class);
        $this->hydrator      = $this->createMock(TicketHydrator::class);
        $this->renderer      = $this->createMock(TemplateRendererInterface::class);
        $this->urlHelper     = $this->createMock(UrlHelper::class);

        $this->handler = new ViewTicketHandler(
            $this->ticketService,
            $this->hydrator,
            $this->renderer,
            $this->urlHelper
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new ViewTicketHandler(
            $this->ticketService,
            $this->hydrator,
            $this->renderer,
            $this->urlHelper
        );

        $this->assertInstanceOf(ViewTicketHandler::class, $handler);
    }

    public function testHandleGetRequestRendersTicketView(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);

        $ticket->method('getContact')->willReturn($contact);

        $responses     = [$this->createMock(TicketResponse::class)];
        $recentTickets = [$this->createMock(Ticket::class)];

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->expects($this->once())
            ->method('getTicketByUuid')
            ->with('ticket-uuid-123')
            ->willReturn($ticket);

        $this->ticketService->expects($this->once())
            ->method('findTicketResponses')
            ->with(456)
            ->willReturn($responses);

        $this->ticketService->expects($this->once())
            ->method('findRecentTicketsByContact')
            ->with(789)
            ->willReturn($recentTickets);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::view-ticket',
                $this->callback(function ($data) use ($ticket, $responses, $recentTickets) {
                    return isset($data['ticket']) && $data['ticket'] === $ticket &&
                           isset($data['responses']) && $data['responses'] === $responses &&
                           isset($data['recentTickets']) && $data['recentTickets'] === $recentTickets &&
                           isset($data['responseForm']) && $data['responseForm'] instanceof TicketResponseForm;
                })
            )
            ->willReturn('<html>Ticket view</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Ticket view</html>', $response->getBody()->getContents());
    }

    public function testHandlePostRequestWithValidDataRedirectsToTicketList(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        $postData = [
            'response'      => 'This is a test response',
            'ticket_status' => '2',
            'submit'        => 'save',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);
        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);

        $expectedData = [
            'response'  => 'This is a test response',
            'submit'    => 'save',
            'agent_id'  => 123,
            'id'        => null,
            'is_public' => null,
        ];

        $this->ticketService->expects($this->once())
            ->method('saveResponse')
            ->with($ticket, $expectedData)
            ->willReturn($this->createMock(TicketResponse::class));

        $this->urlHelper->expects($this->once())
            ->method('generate')
            ->with('ticket.list')
            ->willReturn('/tickets');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/tickets', $response->getHeaderLine('Location'));
    }

    public function testHandlePostRequestWithInvalidDataRendersFormWithErrors(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        // Invalid data - empty response
        $postData = [
            'response'      => '',
            'ticket_status' => '',
        ];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);
        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);

        // saveResponse should not be called for invalid data
        $this->ticketService->expects($this->never())
            ->method('saveResponse');

        $this->renderer->expects($this->once())
            ->method('render')
            ->with('ticket::view-ticket', $this->isType('array'))
            ->willReturn('<html>Ticket view with errors</html>');

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Ticket view with errors</html>', $response->getBody()->getContents());
    }

    public function testHandleRetrievesTicketByUuidFromRequest(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        $ticketUuid = 'specific-ticket-uuid';

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', $ticketUuid);

        $this->ticketService->expects($this->once())
            ->method('getTicketByUuid')
            ->with($ticketUuid)
            ->willReturn($ticket);

        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);
        $this->renderer->method('render')->willReturn('');

        $this->handler->handle($request);
    }

    public function testHandleRetrievesAgentIdFromUser(): void
    {
        $agentId = 987;
        $user    = $this->createMockUser($agentId);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        $postData = ['response' => 'Test response for agent test', 'ticket_status' => '2', 'submit' => 'save'];

        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);
        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);

        $expectedData = [
            'response'  => 'Test response for agent test',
            'submit'    => 'save',
            'agent_id'  => $agentId,
            'id'        => null,
            'is_public' => null,
        ];

        $this->ticketService->expects($this->once())
            ->method('saveResponse')
            ->with($ticket, $expectedData);

        $this->urlHelper->method('generate')->willReturn('/tickets');

        $this->handler->handle($request);
    }

    public function testHandleLoadsTicketResponsesAndRecentTickets(): void
    {
        $user      = $this->createMockUser(123);
        $ticket    = $this->createMockTicket(456);
        $contact   = $this->createMock(Contact::class);
        $contactId = 789;
        $contact->method('getId')->willReturn($contactId);
        $ticket->method('getContact')->willReturn($contact);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);

        $this->ticketService->expects($this->once())
            ->method('findTicketResponses')
            ->with(456);

        $this->ticketService->expects($this->once())
            ->method('findRecentTicketsByContact')
            ->with($contactId);

        $this->renderer->method('render')->willReturn('');

        $this->handler->handle($request);
    }

    public function testHandleCreatesNewTicketResponseForm(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);
        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::view-ticket',
                $this->callback(function ($data) {
                    return isset($data['responseForm']) &&
                           $data['responseForm'] instanceof TicketResponseForm;
                })
            )
            ->willReturn('');

        $this->handler->handle($request);
    }

    public function testHandleUsesCorrectTemplate(): void
    {
        $user    = $this->createMockUser(123);
        $ticket  = $this->createMockTicket(456);
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn(789);
        $ticket->method('getContact')->willReturn($contact);

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'ticket-uuid-123');

        $this->ticketService->method('getTicketByUuid')->willReturn($ticket);
        $this->ticketService->method('findTicketResponses')->willReturn([]);
        $this->ticketService->method('findRecentTicketsByContact')->willReturn([]);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with('ticket::view-ticket', $this->anything())
            ->willReturn('');

        $this->handler->handle($request);
    }

    private function createMockUser(int $id): IdentityInterface
    {
        $user = $this->createMock(IdentityInterface::class);
        $user->method('getId')->willReturn($id);
        return $user;
    }

    private function createMockTicket(int $id): Ticket
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('getId')->willReturn($id);
        return $ticket;
    }
}
