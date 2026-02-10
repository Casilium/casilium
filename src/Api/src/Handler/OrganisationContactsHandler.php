<?php

declare(strict_types=1);

namespace Api\Handler;

use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use OrganisationContact\Entity\Contact;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Service\TicketService;

use function sprintf;

class OrganisationContactsHandler implements RequestHandlerInterface
{
    private TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organisationUuid = $request->getAttribute('org_id', '');

        if ($organisationUuid === '') {
            return new JsonResponse([]);
        }

        try {
            $organisation = $this->ticketService->getOrganisationByUuid($organisationUuid);
        } catch (Exception $e) {
            return new JsonResponse([]);
        }

        $contacts = $this->ticketService->getContactsByOrganisationId($organisation->getId());

        $results = [];
        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            $results[] = [
                'id'   => $contact->getId(),
                'name' => sprintf('%s %s', $contact->getFirstName(), $contact->getLastName()),
            ];
        }

        return new JsonResponse($results);
    }
}
