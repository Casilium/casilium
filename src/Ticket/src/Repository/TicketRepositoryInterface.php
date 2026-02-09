<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Carbon\CarbonInterface;
use Doctrine\ORM\Query;
use Ticket\Entity\Ticket;

interface TicketRepositoryInterface
{
    /**
     * Find ticket by UUID
     *
     * @param string $uuid UUID of the ticket
     * @return Ticket|null Ticket or null if not found
     */
    public function findTicketByUuid(string $uuid): ?Ticket;

    /**
     * Save ticket
     *
     * @param Ticket $ticket Ticket to save
     * @return Ticket Saved ticket is returned
     */
    public function save(Ticket $ticket): Ticket;

    /**
     * @param int $contactId Contact ID
     * @param int $limit number of recent tickets to fetch
     * @return array
     */
    public function findRecentTicketsByContact(int $contactId, $limit = 5): array;

    /**
     * Fetch all tickets from DB (Doctrine default signature).
     *
     * @return array ticket list
     */
    public function findAll(): array;

    /**
     * Fetch all tickets with ability to include/exclude resolved tickets.
     *
     * @param bool $fetchResolved Include closed/resolved tickets
     * @return array
     */
    public function findAllTickets(bool $fetchResolved = true): array;

    /**
     * Fetch tickets from database, paginated
     *
     * @param int $offset page offset
     * @param int $limit number of results to fetch
     * @param array $options array of options to pass
     * @return Query Query to pass to paginator
     */
    public function findTicketsByPagination(array $options = [], int $offset = 0, int $limit = 2): Query;

    /**
     * Fetch tickets belonging to organisation
     *
     * @param string $uuid UUID of organisation
     * @return array of tickets by organisation
     */
    public function findByOrganisationUuid(string $uuid): array;

    public function findUnresolvedTicketCount(): int;

    /**
     * Find average resolution time in hours
     *
     * @param CarbonInterface|null $periodStart Start of period
     * @param CarbonInterface|null $periodEnd End of period
     * @return float Average resolution time in hours
     */
    public function findAverageResolutionTime(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null
    ): float;

    public function findAverageResolutionTimeWithoutSla(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null
    ): float;

    public function findResolvedTicketCountBySlaStatus(
        bool $hasSla,
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null
    ): int;

    /**
     * Find SLA compliance stats for resolved tickets
     *
     * @param CarbonInterface|null $periodStart Start of period
     * @param CarbonInterface|null $periodEnd End of period
     * @param int|null $organisationId Organisation ID to filter
     * @param int|null $type Ticket type ID to filter
     * @return array{total:int,within:int}
     */
    public function findSlaComplianceStats(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null,
        ?int $organisationId = null,
        ?int $type = null
    ): array;

    /**
     * Find SLA compliance rate as a percentage
     *
     * @param CarbonInterface|null $periodStart Start of period
     * @param CarbonInterface|null $periodEnd End of period
     * @param int|null $organisationId Organisation ID to filter
     * @param int|null $type Ticket type ID to filter
     * @return float SLA compliance rate (0-100)
     */
    public function findSlaComplianceRate(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null,
        ?int $organisationId = null,
        ?int $type = null
    ): float;
}
