<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Api\Handler\TicketSearchHandler;
use DateTimeImmutable;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Ticket\Service\TicketService;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class TicketSearchHandlerTest extends TestCase
{
    public function testMatchingTicketHasAStringDateForTheSearchDropdown(): void
    {
        $service = $this->createMock(TicketService::class);
        $service->expects($this->once())->method('searchTickets')->with('printer')->willReturn([
            [
                'id'               => 123,
                'shortDescription' => 'Broken printer',
                'createdAt'        => new DateTimeImmutable('2026-10-23T12:00:00+00:00'),
            ],
        ]);

        $response = (new TicketSearchHandler($service))->handle(
            (new ServerRequest())->withQueryParams(['q' => 'printer'])
        );
        $results  = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('2026-10-23T12:00:00+00:00', $results[0]['createdAt']);
        self::assertSame('Broken printer', $results[0]['shortDescription']);
    }

    public function testNoMatchesReturnsAnEmptyList(): void
    {
        $service = $this->createMock(TicketService::class);
        $service->expects($this->once())->method('searchTickets')->with('missing')->willReturn([]);

        $response = (new TicketSearchHandler($service))->handle(
            (new ServerRequest())->withQueryParams(['q' => 'missing'])
        );

        self::assertSame('[]', (string) $response->getBody());
    }
}
