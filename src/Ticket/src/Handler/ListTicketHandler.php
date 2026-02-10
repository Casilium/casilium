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
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

class ListTicketHandler implements RequestHandlerInterface
{
    /** @var TicketService */
    private $ticketService;

    /** @var TemplateRendererInterface */
    private $renderer;

    public function __construct(TicketService $ticketService, TemplateRendererInterface $renderer)
    {
        $this->ticketService = $ticketService;
        $this->renderer      = $renderer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $page         = TicketListRequest::extractPage($request);
        $options      = TicketListRequest::extractOptions($request);
        $itemsPerPage = TicketListRequest::extractItemsPerPage($request);
        /** @var TicketRepository $repository */
        $repository = $this->ticketService->getEntityManager()->getRepository(Ticket::class);
        $query      = $repository->findTicketsByPagination($options);

        $adapter   = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($page);

        return new HtmlResponse($this->renderer->render('ticket::ticket-list', [
            'tickets'           => $paginator,
            'pageCount'         => $paginator->count(),
            'itemsPerPage'      => $itemsPerPage,
            'changesUrl'        => TicketListRequest::extractChangesPath($request),
            'refreshIntervalMs' => TicketListRequest::POLL_INTERVAL_MS,
        ]));
    }
}
