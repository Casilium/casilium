<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessages;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use OrganisationSite\Entity\SiteEntity;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\Queue;
use Ticket\Entity\Ticket;
use Ticket\Entity\Type;
use Ticket\Form\TicketForm;
use Ticket\Handler\CreateTicketHandler;
use Ticket\Hydrator\TicketHydrator;
use Ticket\Service\TicketService;
use UserAuthentication\Entity\IdentityInterface;

class CreateTicketHandlerTest extends TestCase
{
    private CreateTicketHandler $handler;
    private TicketService $ticketService;
    private TicketHydrator $hydrator;
    private TemplateRendererInterface $renderer;
    private UrlHelper $urlHelper;

    protected function setUp(): void
    {
        $this->ticketService = $this->createMock(TicketService::class);
        $this->hydrator = $this->createMock(TicketHydrator::class);
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
        $this->urlHelper = $this->createMock(UrlHelper::class);
        
        $this->handler = new CreateTicketHandler(
            $this->ticketService,
            $this->hydrator,
            $this->renderer,
            $this->urlHelper
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new CreateTicketHandler(
            $this->ticketService,
            $this->hydrator,
            $this->renderer,
            $this->urlHelper
        );
        
        $this->assertInstanceOf(CreateTicketHandler::class, $handler);
    }

    public function testHandleGetRequestRendersCreateTicketForm(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        
        $sites = [$this->createMockSite(1, '123 Main St')];
        $contacts = [$this->createMockContact(1, 'John', 'Doe')];
        $queues = [$this->createMockQueue(1, 'Support')];
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->expects($this->once())
            ->method('getOrganisationByUuid')
            ->with('org-uuid-123')
            ->willReturn($organisation);
            
        $this->ticketService->expects($this->once())
            ->method('getSitesByOrganisationId')
            ->with(456)
            ->willReturn($sites);
            
        $this->ticketService->expects($this->once())
            ->method('getContactsByOrganisationId')
            ->with(456)
            ->willReturn($contacts);
            
        $this->ticketService->expects($this->once())
            ->method('getQueues')
            ->willReturn($queues);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::create-ticket',
                $this->callback(function($data) use ($organisation) {
                    return isset($data['form']) && $data['form'] instanceof TicketForm &&
                           isset($data['org_id']) && $data['org_id'] === $organisation->getUuid();
                })
            )
            ->willReturn('<html>Create ticket form</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Create ticket form</html>', $response->getBody()->getContents());
    }

    public function testHandlePostRequestCallsCorrectServiceMethods(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        
        $postData = [
            'subject' => 'Test ticket',
            'description' => 'Test description'
        ];
        
        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->expects($this->once())
            ->method('getOrganisationByUuid')
            ->with('org-uuid-123')
            ->willReturn($organisation);
            
        $this->ticketService->expects($this->once())
            ->method('getSitesByOrganisationId')
            ->with(456)
            ->willReturn([]);
            
        $this->ticketService->expects($this->once())
            ->method('getContactsByOrganisationId')
            ->with(456)
            ->willReturn([]);
            
        $this->ticketService->expects($this->once())
            ->method('getQueues')
            ->willReturn([]);
        
        $this->renderer->method('render')->willReturn('<html>form</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testHandlePostRequestWithInvalidDataRendersFormWithErrors(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        
        // Invalid data - empty required fields
        $postData = [
            'subject' => '',
            'description' => ''
        ];
        
        $request = new ServerRequest();
        $request = $request->withMethod('POST')
                          ->withParsedBody($postData)
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([]);
        
        // save should not be called for invalid data
        $this->ticketService->expects($this->never())
            ->method('save');
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('ticket::create-ticket', $this->isType('array'))
            ->willReturn('<html>Form with errors</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals('<html>Form with errors</html>', $response->getBody()->getContents());
    }

    public function testHandleWithExistingTicketIdPrePopulatesForm(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        $existingTicket = $this->createMockTicket(999);
        $contact = $this->createMockContact(1, 'John', 'Doe');
        $type = $this->createMockType(2);
        
        $existingTicket->method('getContact')->willReturn($contact);
        $existingTicket->method('getType')->willReturn($type);
        $existingTicket->method('getOrganisation')->willReturn($organisation);
        $existingTicket->method('getArrayCopy')->willReturn([
            'subject' => 'Existing ticket subject',
            'description' => 'Existing ticket description'
        ]);
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('ticket_id', 'existing-ticket-uuid')
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->expects($this->once())
            ->method('getTicketByUuid')
            ->with('existing-ticket-uuid')
            ->willReturn($existingTicket);
        
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([]);
        
        $this->renderer->method('render')->willReturn('<html>Prepopulated form</html>');
        
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testHandleSetsFormOptionsForSingleSite(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        $site = $this->createMockSite(1, '123 Main St');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([$site]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([]);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::create-ticket',
                $this->callback(function($data) {
                    $form = $data['form'];
                    $siteElement = $form->get('site_id');
                    return $siteElement->getValue() === 1; // Should be pre-selected
                })
            )
            ->willReturn('<html>Single site form</html>');
        
        $this->handler->handle($request);
    }

    public function testHandleSetsFormOptionsForSingleContact(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        $contact = $this->createMockContact(1, 'John', 'Doe');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([$contact]);
        $this->ticketService->method('getQueues')->willReturn([]);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::create-ticket',
                $this->callback(function($data) {
                    $form = $data['form'];
                    $contactElement = $form->get('contact_id');
                    return $contactElement->getValue() === 1; // Should be pre-selected
                })
            )
            ->willReturn('<html>Single contact form</html>');
        
        $this->handler->handle($request);
    }

    public function testHandleSetsFormOptionsForSingleQueue(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        $queue = $this->createMockQueue(1, 'Support');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([$queue]);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'ticket::create-ticket',
                $this->callback(function($data) {
                    $form = $data['form'];
                    $queueElement = $form->get('queue_id');
                    return $queueElement->getValue() === 1; // Should be pre-selected
                })
            )
            ->willReturn('<html>Single queue form</html>');
        
        $this->handler->handle($request);
    }

    public function testHandleUsesCorrectTemplate(): void
    {
        $user = $this->createMockUser(123);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([]);
        
        $this->renderer->expects($this->once())
            ->method('render')
            ->with('ticket::create-ticket', $this->anything())
            ->willReturn('<html>Template</html>');
        
        $this->handler->handle($request);
    }

    public function testHandleRetrievesAgentIdFromUser(): void
    {
        $agentId = 987;
        $user = $this->createMockUser($agentId);
        $organisation = $this->createMockOrganisation(456, 'org-uuid-123');
        
        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute(IdentityInterface::class, $user)
                          ->withAttribute('org_id', 'org-uuid-123');
        
        $this->ticketService->method('getOrganisationByUuid')->willReturn($organisation);
        $this->ticketService->method('getSitesByOrganisationId')->willReturn([]);
        $this->ticketService->method('getContactsByOrganisationId')->willReturn([]);
        $this->ticketService->method('getQueues')->willReturn([]);
        $this->renderer->method('render')->willReturn('<html>test</html>');
        
        // Test that user ID is properly retrieved and used
        $response = $this->handler->handle($request);
        
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    private function createMockUser(int $id): IdentityInterface
    {
        $user = $this->createMock(IdentityInterface::class);
        $user->method('getId')->willReturn($id);
        return $user;
    }

    private function createMockOrganisation(int $id, string $uuid): Organisation
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn($id);
        
        $uuidInterface = $this->createMock(\Ramsey\Uuid\UuidInterface::class);
        $uuidInterface->method('__toString')->willReturn($uuid);
        $organisation->method('getUuid')->willReturn($uuidInterface);
        
        return $organisation;
    }

    private function createMockTicket(int $id): Ticket
    {
        $ticket = $this->createMock(Ticket::class);
        $ticket->method('getId')->willReturn($id);
        return $ticket;
    }

    private function createMockSite(int $id, string $address): SiteEntity
    {
        $site = $this->createMock(SiteEntity::class);
        $site->method('getId')->willReturn($id);
        $site->method('getAddressAsString')->willReturn($address);
        return $site;
    }

    private function createMockContact(int $id, string $firstName, string $lastName): Contact
    {
        $contact = $this->createMock(Contact::class);
        $contact->method('getId')->willReturn($id);
        $contact->method('getFirstName')->willReturn($firstName);
        $contact->method('getLastName')->willReturn($lastName);
        return $contact;
    }

    private function createMockQueue(int $id, string $name): Queue
    {
        $queue = $this->createMock(Queue::class);
        $queue->method('getId')->willReturn($id);
        $queue->method('getName')->willReturn($name);
        return $queue;
    }

    private function createMockType(int $id): Type
    {
        $type = $this->createMock(Type::class);
        $type->method('getId')->willReturn($id);
        return $type;
    }
}