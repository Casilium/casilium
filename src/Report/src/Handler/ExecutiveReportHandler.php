<?php

declare(strict_types=1);

namespace Report\Handler;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Organisation\Entity\Organisation;
use Organisation\Exception\OrganisationNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Report\Service\PdfService;
use Report\Service\ReportService;
use Ticket\Entity\Type;

use function preg_replace;
use function sprintf;

class ExecutiveReportHandler implements RequestHandlerInterface
{
    private PdfService $pdfService;

    private ReportService $reportService;

    /** @var array<string, mixed> */
    private array $reportConfig;

    /**
     * @param array<string, mixed> $reportConfig
     */
    public function __construct(ReportService $reportService, PdfService $pdfService, array $reportConfig = [])
    {
        $this->reportService = $reportService;
        $this->pdfService    = $pdfService;
        $this->reportConfig  = $reportConfig;
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
        $stats['incidentSla']      = $this->reportService->getIncidentSlaComplianceStats();

        $unresolvedTickets = [];
        if (($this->reportConfig['include_unresolved'] ?? false) === true) {
            $limit             = (int) ($this->reportConfig['unresolved_limit'] ?? 20);
            $unresolvedTickets = $this->reportService->getUnresolvedTickets($limit);
        }

        // generate PDF
        $pdfContent = $this->pdfService->generateExecutiveReport(
            $stats,
            $organisation,
            $this->reportService->getStartDate(),
            $this->reportService->getEndDate(),
            $this->reportConfig,
            $unresolvedTickets
        );

        // build filename
        $orgName  = preg_replace('/[^A-Za-z0-9\-]/', '-', $organisation->getName());
        $month    = $this->reportService->getStartDate()->format('Y-m');
        $filename = sprintf('Executive-Report-%s-%s.pdf', $orgName, $month);

        // return PDF response
        $stream = new Stream('php://memory', 'w');
        $stream->write($pdfContent);

        $response = new Response();
        return $response
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', sprintf('inline; filename="%s"', $filename))
            ->withBody($stream);
    }
}
