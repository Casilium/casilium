<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\Ticket;
use Ticket\Handler\TicketListChangesHandler;
use Ticket\Handler\TicketListRequest;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

use function json_decode;
use function strlen;

class TicketListChangesHandlerTest extends TestCase
{
    public function testHandleReturnsFingerprintPayload(): void
    {
        $ticketService = $this->createMock(TicketService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository    = $this->createMock(TicketRepository::class);

        $ticketService->method('getEntityManager')->willReturn($entityManager);
        $entityManager->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findTicketListSignatureData')
            ->with(['hide_completed' => true], 0, TicketListRequest::ITEMS_PER_PAGE)
            ->willReturn([
                [
                    'id'        => 123,
                    'status_id' => 1,
                ],
            ]);

        $handler  = new TicketListChangesHandler($ticketService);
        $response = $handler->handle(new ServerRequest());
        $payload  = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('fingerprint', $payload);
        $this->assertArrayHasKey('generatedAt', $payload);
        $this->assertSame(64, strlen($payload['fingerprint']));
        $this->assertSame('no-store, no-cache, must-revalidate', $response->getHeaderLine('Cache-Control'));
    }

    public function testHandleUsesPageOffsetAndOrganisationFilter(): void
    {
        $ticketService = $this->createMock(TicketService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository    = $this->createMock(TicketRepository::class);

        $ticketService->method('getEntityManager')->willReturn($entityManager);
        $entityManager->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findTicketListSignatureData')
            ->with(
                [
                    'organisation_uuid' => 'org-uuid-123',
                    'hide_completed'    => true,
                ],
                50,
                TicketListRequest::ITEMS_PER_PAGE
            )
            ->willReturn([]);

        $request = (new ServerRequest())
            ->withAttribute('org_id', 'org-uuid-123')
            ->withQueryParams([
                'page' => '3',
            ]);

        $handler = new TicketListChangesHandler($ticketService);
        $handler->handle($request);
    }

    public function testHandleUsesRowsQueryParamForOffsetAndLimit(): void
    {
        $ticketService = $this->createMock(TicketService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository    = $this->createMock(TicketRepository::class);

        $ticketService->method('getEntityManager')->willReturn($entityManager);
        $entityManager->method('getRepository')
            ->with(Ticket::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findTicketListSignatureData')
            ->with(
                ['hide_completed' => true],
                20,
                10
            )
            ->willReturn([]);

        $request = (new ServerRequest())->withQueryParams([
            'page' => '3',
            'rows' => '10',
        ]);

        $handler = new TicketListChangesHandler($ticketService);
        $handler->handle($request);
    }
}
