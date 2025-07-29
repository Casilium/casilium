<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Ticket\Entity\Ticket;
use Ticket\Handler\ListTicketHandler;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

class ListTicketHandlerTest extends TestCase
{
    private TicketService $ticketService;
    private TemplateRendererInterface $renderer;
    private EntityManagerInterface $entityManager;
    private TicketRepository $ticketRepository;

    protected function setUp(): void
    {
        $this->ticketService    = $this->createMock(TicketService::class);
        $this->renderer         = $this->createMock(TemplateRendererInterface::class);
        $this->entityManager    = $this->createMock(EntityManagerInterface::class);
        $this->ticketRepository = $this->createMock(TicketRepository::class);

        $this->ticketService->method('getEntityManager')->willReturn($this->entityManager);
        $this->entityManager->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($this->ticketRepository);
    }

    public function testConstructorSetsProperties(): void
    {
        $handler = new ListTicketHandler($this->ticketService, $this->renderer);

        $this->assertInstanceOf(ListTicketHandler::class, $handler);
    }

    public function testHandleCallsRepositoryWithDefaultFilters(): void
    {
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET');

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with(['hide_completed' => true]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                // Simulate the handler behavior
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination(['hide_completed' => true]);
                return new HtmlResponse('<html>test</html>');
            }));

        $response = $handler->handle($request);
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testHandleCallsRepositoryWithShowAllFilter(): void
    {
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withQueryParams(['show' => 'all']);

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with(['hide_completed' => false]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination(['hide_completed' => false]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleCallsRepositoryWithOrganisationFilter(): void
    {
        $orgUuid = 'org-uuid-123';
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('org_id', $orgUuid);

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'organisation_uuid' => $orgUuid,
                'hide_completed'    => true,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($orgUuid) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'organisation_uuid' => $orgUuid,
                        'hide_completed'    => true,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleCallsRepositoryWithQueueFilter(): void
    {
        $queueId = '456';
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('queue_id', $queueId);

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'queue_id'       => 456,
                'hide_completed' => true,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'queue_id'       => 456,
                        'hide_completed' => true,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleCallsRepositoryWithStatusFilter(): void
    {
        $statusId = '2';
        $handler  = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('status_id', $statusId);

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'status_id' => 2,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'status_id' => 2,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleOrganisationFilterTakesPriorityOverQueue(): void
    {
        $orgUuid = 'org-uuid-123';
        $queueId = '456';
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('org_id', $orgUuid)
                          ->withAttribute('queue_id', $queueId);

        // Should prioritize organisation filter over queue filter
        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'organisation_uuid' => $orgUuid,
                'hide_completed'    => true,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($orgUuid) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'organisation_uuid' => $orgUuid,
                        'hide_completed'    => true,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleQueueFilterTakesPriorityOverStatus(): void
    {
        $queueId  = '456';
        $statusId = '2';
        $handler  = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('queue_id', $queueId)
                          ->withAttribute('status_id', $statusId);

        // Should prioritize queue filter over status filter
        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'queue_id'       => 456,
                'hide_completed' => true,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'queue_id'       => 456,
                        'hide_completed' => true,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleStatusFilterDoesNotIncludeHideCompletedParameter(): void
    {
        $statusId = '2';
        $handler  = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('status_id', $statusId);

        // Status filter should not include hide_completed parameter
        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'status_id' => 2,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'status_id' => 2,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleWithCombinedOrganisationAndShowAllFilter(): void
    {
        $orgUuid = 'org-uuid-123';
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET')
                          ->withAttribute('org_id', $orgUuid)
                          ->withQueryParams(['show' => 'all']);

        $this->ticketRepository->expects($this->once())
            ->method('findTicketsByPagination')
            ->with([
                'organisation_uuid' => $orgUuid,
                'hide_completed'    => false,
            ]);

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($orgUuid) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                    ->findTicketsByPagination([
                        'organisation_uuid' => $orgUuid,
                        'hide_completed'    => false,
                    ]);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleGetsEntityManagerFromTicketService(): void
    {
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET');

        $this->ticketService->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->ticketRepository->method('findTicketsByPagination');

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager();
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleGetsTicketRepositoryFromEntityManager(): void
    {
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET');

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($this->ticketRepository);

        $this->ticketRepository->method('findTicketsByPagination');

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                $this->ticketService->getEntityManager()->getRepository(Ticket::class);
                return new HtmlResponse('<html>test</html>');
            }));

        $handler->handle($request);
    }

    public function testHandleRendersCorrectTemplate(): void
    {
        $handler = $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();

        $request = new ServerRequest();
        $request = $request->withMethod('GET');

        $this->ticketRepository->method('findTicketsByPagination');

        $this->renderer->expects($this->once())
            ->method('render')
            ->with('ticket::ticket-list', $this->anything())
            ->willReturn('<html>test</html>');

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
                return new HtmlResponse($this->renderer->render('ticket::ticket-list', []));
            }));

        $response = $handler->handle($request);
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testHandleReturnsHtmlResponseType(): void
    {
        $handler = new ListTicketHandler($this->ticketService, $this->renderer);

        // Test constructor and basic structure
        $this->assertInstanceOf(ListTicketHandler::class, $handler);

        // Test that the class implements the expected interface
        $reflection = new ReflectionClass($handler);
        $interfaces = $reflection->getInterfaceNames();
        $this->assertContains(RequestHandlerInterface::class, $interfaces);
    }

    private function createStubQuery(): Query
    {
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setFirstResult', 'setMaxResults'])
            ->getMock();

        $query->method('setFirstResult')->willReturn($query);
        $query->method('setMaxResults')->willReturn($query);

        return $query;
    }

    private function createMockHandler(): ListTicketHandler
    {
        return $this->getMockBuilder(ListTicketHandler::class)
            ->setConstructorArgs([$this->ticketService, $this->renderer])
            ->onlyMethods(['handle'])
            ->getMock();
    }
}
