<?php

declare(strict_types=1);

namespace Report\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Organisation\Entity\Organisation;
use Organisation\Exception\OrganisationNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Report\Service\ReportService;
use Ticket\Entity\Type;

/**
 * Handler to provide Executive Reports to be presented to clients.
 */
class ExecutiveReportHandler implements RequestHandlerInterface
{
    /** @var TemplateRendererInterface */
    private $renderer;

    /** @var ReportService */
    private $reportService;

    public function __construct(ReportService $reportService, TemplateRendererInterface $renderer)
    {
        $this->renderer      = $renderer;
        $this->reportService = $reportService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // find organisation from uuid passed via url
        $organisationUuid = $request->getAttribute('org_id');
        $organisation     = $this->reportService->findOrganisationByUuid($organisationUuid);
        if (! $organisation instanceof Organisation) {
            throw OrganisationNotFoundException::whenSearchingByUuid($organisationUuid);
        }

        // set organisation for report
        $this->reportService->setOrganisation($organisation);

        // build stats required pulling from database
        $stats = [
            'totalIncident'    => $this->reportService->getTotalTicketCount(['type' => Type::TYPE_INCIDENT]),
            'totalRequest'     => $this->reportService->getTotalTicketCount(['type' => Type::TYPE_REQUEST]),
            'resolvedIncident' => $this->reportService->getResolvedTicketCount(['type' => Type::TYPE_INCIDENT]),
            'resolvedRequest'  => $this->reportService->getResolvedTicketCount(['type' => Type::TYPE_REQUEST]),
            'closedIncident'   => $this->reportService->getClosedTicketCount(['type' => Type::TYPE_INCIDENT]),
            'closedRequest'    => $this->reportService->getClosedTicketCount(['type' => Type::TYPE_REQUEST]),
            'holdIncident'     => $this->reportService->getHoldTicketCount(['type' => Type::TYPE_INCIDENT]),
            'holdRequest'      => $this->reportService->getHoldTicketCount(['type' => Type::TYPE_REQUEST]),
            'progressIncident' => $this->reportService->getTicketInProgressCount(['type' => Type::TYPE_INCIDENT]),
            'progressRequest'  => $this->reportService->getTicketInProgressCount(['type' => Type::TYPE_REQUEST]),
            'newIncident'      => $this->reportService->getNewTicketCount(['type' => Type::TYPE_INCIDENT]),
            'newRequest'       => $this->reportService->getNewTicketCount(['type' => Type::TYPE_REQUEST]),
        ];

        // add stats based on above, not requiring database pull
        $stats += [
            'total'                 => $stats['totalIncident'] + $stats['totalRequest'],
            'resolved'              => $stats['resolvedIncident'] + $stats['resolvedRequest'],
            'closed'                => $stats['closedIncident'] + $stats['closedRequest'],
            'hold'                  => $stats['holdIncident'] + $stats['holdRequest'],
            'progress'              => $stats['progressIncident'] + $stats['progressRequest'],
            'new'                   => $stats['newIncident'] + $stats['newRequest'],
            'totalIncidentComplete' => $stats['resolvedIncident'] + $stats['closedIncident'],
            'totalRequestComplete'  => $stats['resolvedRequest'] + $stats['closedRequest'],
            'totalComplete'         => $stats['resolvedIncident'] + $stats['closedIncident']
                                     + $stats['resolvedRequest'] + $stats['closedRequest'],
        ];

        // stats based on above stats being present in stats array
        $stats['totalOutstanding'] = $stats['new'] + $stats['progress'] + $stats['hold'];

        // return the HTML response, passing stats and organisation to the view
        return new HtmlResponse($this->renderer->render('report::executive-report', [
            'stats'        => $stats,
            'organisation' => $organisation,
            'startDate'    => $this->reportService->getStartDate(),
            'endDate'      => $this->reportService->getEndDate(),
        ]));
    }
}
