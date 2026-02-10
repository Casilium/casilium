<?php

declare(strict_types=1);

namespace Ticket\Handler;

use App\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Paginator\Paginator;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Ticket;
use Ticket\InputFilter\SearchTicketInputFilter;
use Ticket\Service\TicketService;

use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function in_array;
use function is_array;

class SearchTicketHandler implements RequestHandlerInterface
{
    private TicketService $ticketService;

    private TemplateRendererInterface $renderer;

    public function __construct(TicketService $ticketService, TemplateRendererInterface $renderer)
    {
        $this->ticketService = $ticketService;
        $this->renderer      = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $page        = (int) ($queryParams['page'] ?? 1);

        $inputFilter = new SearchTicketInputFilter();
        $inputFilter->init();
        $inputFilter->setData($queryParams);
        $inputFilter->isValid();

        // only use values from inputs that passed validation
        $invalidFields = array_keys($inputFilter->getMessages());
        $validatedData = $inputFilter->getValues();

        $filters    = [];
        $filterKeys = [
            'search_text',
            'organisation_uuid',
            'contact_id',
            'queue_id',
            'date_from',
            'date_to',
        ];

        foreach ($filterKeys as $key) {
            if (in_array($key, $invalidFields, true)) {
                continue;
            }
            $value = $validatedData[$key] ?? '';
            if ($value !== '' && $value !== 0) {
                $filters[$key] = $value;
            }
        }

        // sanitise status_id array separately
        $rawStatuses = $queryParams['status_id'] ?? [];
        if (is_array($rawStatuses)) {
            $statusIds = array_values(array_filter(
                array_map('intval', $rawStatuses),
                static fn (int $id): bool => $id > 0
            ));
            if ($statusIds !== []) {
                $filters['status_id'] = $statusIds;
            }
        }

        $options = $filters;

        // include all statuses when no specific status filter is set
        if (! isset($options['status_id'])) {
            $options['hide_completed'] = false;
        }

        $query = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
            ->findTicketsByPagination($options);

        $adapter   = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($page);

        return new HtmlResponse($this->renderer->render('ticket::search-ticket', [
            'tickets'       => $paginator,
            'pageCount'     => $paginator->count(),
            'filters'       => $filters,
            'statuses'      => $this->ticketService->getStatuses(),
            'queues'        => $this->ticketService->getQueues(),
            'organisations' => $this->ticketService->getOrganisations(),
        ]));
    }
}
