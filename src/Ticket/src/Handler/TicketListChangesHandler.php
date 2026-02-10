<?php

declare(strict_types=1);

namespace Ticket\Handler;

use DateTimeImmutable;
use DateTimeZone;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

use function hash;
use function json_encode;
use function ksort;

use const DATE_ATOM;

class TicketListChangesHandler implements RequestHandlerInterface
{
    private TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $page         = TicketListRequest::extractPage($request);
        $options      = TicketListRequest::extractOptions($request);
        $itemsPerPage = TicketListRequest::extractItemsPerPage($request);
        $offset       = ($page - 1) * $itemsPerPage;

        /** @var TicketRepository $repository */
        $repository = $this->ticketService->getEntityManager()->getRepository(Ticket::class);
        $rows       = $repository->findTicketListSignatureData(
            $options,
            $offset,
            $itemsPerPage
        );

        ksort($options);
        $payload = [
            'page'           => $page,
            'items_per_page' => $itemsPerPage,
            'options'        => $options,
            'rows'           => $rows,
        ];

        $response = new JsonResponse([
            'fingerprint' => hash('sha256', (string) json_encode($payload)),
            'generatedAt' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_ATOM),
        ]);

        return $response
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Pragma', 'no-cache');
    }
}
