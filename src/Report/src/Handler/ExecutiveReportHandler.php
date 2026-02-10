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

        $stats = $this->reportService->buildExecutiveStats();

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
