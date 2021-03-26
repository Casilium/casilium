<?php

declare(strict_types=1);

namespace Report\Service;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Organisation\Entity\Organisation;
use Organisation\Service\OrganisationManager;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;
use Ticket\Repository\TicketRepositoryInterface;
use function array_merge;

class ReportService
{
    /** @var Organisation */
    private $organisation;

    /** @var OrganisationManager */
    private $organisationManager;

    /** @var array */
    private $options;

    /** @var array */
    private $reportDate;

    /** @var TicketRepository */
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository, OrganisationManager $organisationManager)
    {
        $this->ticketRepository    = $ticketRepository;
        $this->organisationManager = $organisationManager;

        // set default reporting period
        $this->setReportDate();
    }

    /**
     * Set the organisation report is related to
     *
     * @param Organisation $organisation Organisation report is provided for
     */
    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * Return the organisation relating to this report
     *
     * @return Organisation Organisation relating to the report
     */
    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    /**
     * Find an organisation from database by organisation uuid
     *
     * @param string $uuid uuid of organisation to find
     * @return Organisation Organisation
     */
    public function findOrganisationByUuid(string $uuid): Organisation
    {
        return $this->organisationManager->findOrganisationByUuid($uuid);
    }

    /**
     * Return the total number of tickets created during reporting date period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets created
     */
    public function getTotalTicketCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions());
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Return the total number of tickets resolved during reporting date period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets resolved
     */
    public function getResolvedTicketCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions() + ['status' => Ticket::STATUS_RESOLVED]);
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Return the total number of tickets closed during reporting date period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets closed
     */
    public function getClosedTicketCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions() + ['status' => Ticket::STATUS_CLOSED]);
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Return the total number of tickets on hold during reporting date period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets held
     */
    public function getHoldTicketCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions() + ['status' => Ticket::STATUS_ON_HOLD]);
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Return the total number of tickets currently set in-progress during reporting date period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets in-progress
     */
    public function getTicketInProgressCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions() + ['status' => Ticket::STATUS_IN_PROGRESS]);
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Return the total number of unassigned (new) tickets during reporting period
     *
     * @param array $options options to pass to database engine
     * @return int Total number of tickets unassigned
     */
    public function getNewTicketCount(array $options = []): int
    {
        $options = array_merge($options, $this->getDefaultOptions() + ['status' => Ticket::STATUS_NEW]);
        return $this->ticketRepository->findTicketCount($options);
    }

    /**
     * Set the reporting date, if no report date is specified then set defaults
     *
     * @param CarbonInterface|null $startDate Date to report from
     * @param CarbonInterface|null $endDate Date to report to
     */
    public function setReportDate(?CarbonInterface $startDate = null, ?CarbonInterface $endDate = null): void
    {
        // no start or end date specified? Return the defaults
        if (null === $startDate || null === $endDate) {
            $this->reportDate = [
                'start' => Carbon::now('UTC')->subMonth()->startOfMonth(),
                'end'   => Carbon::now('UTC')->subMonth()->endOfMonth(),
            ];
            return;
        }

        // return start and end dates
        $this->reportDate = [
            'start' => $startDate,
            'end'   => $endDate,
        ];
    }

    /**
     * Return the start date of the report
     *
     * @return CarbonInterface Report start date
     */
    public function getStartDate(): CarbonInterface
    {
        return $this->reportDate['start'];
    }

    /**
     * Return the end date for the report
     *
     * @return CarbonInterface Report end date
     */
    public function getEndDate(): CarbonInterface
    {
        return $this->reportDate['end'];
    }

    /**
     * Set up default options to be passed to database engine
     *
     * @return array default options
     */
    public function getDefaultOptions(): array
    {
        if (empty($this->options)) {
            $this->setDefaultOptions();
        }

        return $this->options;
    }

    /**
     * Set default options to be passed to database queries
     *
     * @param array $options options
     */
    public function setDefaultOptions(array $options = []): void
    {
        $this->options = array_merge($options, [
            'start'        => $this->getStartDate(),
            'end'          => $this->getEndDate(),
            'organisation' => $this->getOrganisation()->getId(),
        ]);
    }
}
