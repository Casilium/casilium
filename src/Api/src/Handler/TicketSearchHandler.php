<?php

declare(strict_types=1);

namespace Api\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Service\TicketService;

use function strlen;
use function trim;

class TicketSearchHandler implements RequestHandlerInterface
{
    private TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = trim($request->getQueryParams()['q'] ?? '');

        if ($query === '' || strlen($query) < 3) {
            return new JsonResponse([]);
        }

        $results = $this->ticketService->searchTickets($query);

        return new JsonResponse($results);
    }
}
