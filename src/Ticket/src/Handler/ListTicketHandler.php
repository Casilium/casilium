<?php

namespace Ticket\Handler;

use App\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Paginator\Paginator;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ticket\Entity\Ticket;
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
        $queryParams = $request->getQueryParams();
        $page        = $queryParams['page'] ?? 1;

        if ($organisationUuid = $request->getAttribute('org_id')) {
            $query = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                ->findTicketsByPagination(['organisation_uuid' => $organisationUuid]);
        } elseif ($queueId = $request->getAttribute('queue_id')) {
            $query = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                ->findTicketsByPagination(['queue_id' => (int) $queueId]);
        } elseif ($statusId = $request->getAttribute('status_id')) {
            $query = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                ->findTicketsByPagination(['status_id' => (int) $queueId]);
        } else {
            $query = $this->ticketService->getEntityManager()->getRepository(Ticket::class)
                ->findTicketsByPagination();
        }

        $adapter   = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($page);

        return new HtmlResponse($this->renderer->render('ticket::ticket-list', [
            'tickets'   => $paginator,
            'pageCount' => $paginator->count(),
        ]));
    }
}
