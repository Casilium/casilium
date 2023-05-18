<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Exception;
use Ticket\Entity\Agent;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Entity\TicketResponse;
use Ticket\Service\TicketService;
use function array_map;
use function intval;

class TicketRepository extends EntityRepository implements TicketRepositoryInterface
{
    /**
     * Find ticket by UUID
     *
     * @param string $uuid UUID of the ticket
     * @return Ticket|null Ticket or null if not found
     */
    public function findTicketByUuid(string $uuid): ?Ticket
    {
        return $this->getEntityManager()
            ->createQuery('SELECT t FROM Ticket\Entity\Ticket t WHERE t.uuid = ?1')
            ->setParameter(1, $uuid)
            ->getSingleResult();
    }

    /**
     * Save ticket
     *
     * @param Ticket $ticket Ticket to save
     * @return Ticket Saved ticket is returned
     */
    public function save(Ticket $ticket): Ticket
    {
        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();

        return $ticket;
    }

    /**
     * @param int $contactId Contact ID
     * @param int $limit number of recent tickets to fetch
     * @return array
     */
    public function findRecentTicketsByContact(int $contactId, $limit = 5): array
    {
        $sql   = 'SELECT t FROM Ticket\Entity\Ticket t where t.contact = ?1 ORDER BY t.id DESC';
        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter(1, $contactId)
            ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * Fetch all tickets from DB
     *
     * @param bool $fetchResolved Whether to fetch closed and resolved tickets
     * @return array ticket list
     */
    public function findAll(bool $fetchResolved = true): array
    {
        $qb = $this->createQueryBuilder('q')
            ->select('t')
            ->from(Ticket::class, 't')
            ->orderBy('t.type', 'DESC')
            ->addOrderBy('t.status')
            ->addOrderBy('t.priority')
            ->addOrderBy('t.due_date');

        if ($fetchResolved === false) {
            $qb->where('t.status < :status')
                ->setParameter('status', Ticket::STATUS_RESOLVED);
        }

        return $$qb->getQuery()->getResult();
    }

    /**
     * Fetch tickets from database, paginated
     *
     * @param int $offset page offset
     * @param int $limit number of results to fetch
     * @param array $options array of options to pass
     * @return Query Query to pass to paginator
     */
    public function findTicketsByPagination(array $options = [], int $offset = 0, int $limit = 2): Query
    {
        if (isset($options['hide_completed']) && $options['hide_completed'] === true) {
            // hide completed (resolved/closed) tickets?
            $status = Ticket::STATUS_ON_HOLD;
        } else {
            // by default show all tickets
            $status = Ticket::STATUS_CLOSED;
        }

        // get query builder
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['t'])
            ->from(Ticket::class, 't')
            ->orderBy('t.type', 'DESC')
            ->addOrderBy('t.status')
            ->addOrderBy('t.priority')
            ->addOrderBy('t.due_date')
            ->where('t.status <= :status')
            ->setParameter('status', $status);

        // if list queue show tickets for queue
        if (isset($options['queue_id'])) {
            $queueId = (int) $options['queue_id'];
            $qb->andWhere('t.queue = :queue')
                ->setParameter('queue', $queueId);
        }

        // if requesting particular status, override status
        if (isset($options['status_id'])) {
            $statusId = (int) $options['status_id'];
            $qb->where('t.status = :status')
                ->setParameter('status', $statusId);
        }

        // if organisation uuid is defined, grab that organisation only
        if (isset($options['organisation_uuid'])) {
            $organisationUuid = $options['organisation_uuid'];
            $qb->leftJoin('t.organisation', 'o')
                ->andWhere('o.uuid = :uuid')
                ->setParameter('uuid', $organisationUuid);
        }

        return $qb->getQuery()->setMaxResults($limit)->setFirstResult($offset);
    }

    /**
     * Fetch tickets belonging to organisation
     *
     * @param string $uuid UUID of organisation
     * @return array of tickets by organisation
     */
    public function findByOrganisationUuid(string $uuid): array
    {
        $qb = $this->createQueryBuilder('q');

        $qb->select('t')
            ->from(Ticket::class, 't')
            ->where('o.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->leftJoin('t.organisation', 'o')
            ->orderBy('t.status')
            ->addOrderBy('t.priority')
            ->addOrderBy('t.due_date');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find unresolved ticket count
     *
     * @return int unresolved count
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findUnresolvedTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status IN (:ids)')
            ->setParameter('ids', [
                Ticket::STATUS_NEW,
                Ticket::STATUS_IN_PROGRESS,
            ])
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findDueTodayTicketCount(): int
    {
        $today = new DateTime('now', new DateTimeZone('UTC'));

        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.due_date BETWEEN :dateMin AND :dateMax')
            ->setParameter('dateMin', $today->format('Y-m-d 00:00:00'))
            ->setParameter('dateMax', $today->format('Y-m-d 23:59:59'))
            ->andWhere('t.status < :status')
            ->setParameter('status', Ticket::STATUS_RESOLVED)
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findOverdueTicketCount(): int
    {
        $today = new DateTime('now', new DateTimeZone('UTC'));

        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.due_date < :date')
            ->setParameter('date', $today->format('Y-m-d H:i:s'))
            ->andWhere('t.status < :status')
            ->setParameter('status', Ticket::STATUS_RESOLVED)
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findOpenTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', Ticket::STATUS_NEW)
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findOnHoldTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', Ticket::STATUS_ON_HOLD)
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    /**
     * Find total ticket count, to find between a period pass options as array.
     * Example: ['start' => '2020-01-01', 'end' => 2020-01-31']
     *
     * @param array $options optional start/end, defaults to all time
     * @return int number of tickets
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findTotalTicketCount(array $options = []): int
    {
        $start        = isset($options['start']) ? Carbon::parse($options['start'], 'UTC') : null;
        $end          = isset($options['end']) ? Carbon::parse($options['end'], 'UTC') : null;
        $organisation = isset($options['organisation']) ? intval($options['organisation']) : null;

        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if ($start !== null && $end !== null) {
            $qb->andWhere('t.createdAt BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $start->format('Y-m-d 00:00:00'))
                ->setParameter('dateMax', $end->format('Y-m-d 23:59:59'));
        }

        if (null !== $organisation) {
            $qb->andWhere('t.organisation = :organisation')
                ->setParameter('organisation', $organisation);
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function findTicketCount(array $options = []): int
    {
        $start        = isset($options['start']) ? Carbon::parse($options['start'], 'UTC') : null;
        $end          = isset($options['end']) ? Carbon::parse($options['end'], 'UTC') : null;
        $organisation = isset($options['organisation']) ? intval($options['organisation']) : null;
        $status       = isset($options['status']) ? intval($options['status']) : null;
        $type         = isset($options['type']) ? intval($options['type']) : null;

        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if ($start !== null && $end !== null) {
            $qb->andWhere('t.createdAt BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $start->format('Y-m-d 00:00:00'))
                ->setParameter('dateMax', $end->format('Y-m-d 23:59:59'));
        }

        if (null !== $organisation) {
            $qb->andWhere('t.organisation = :organisation')
                ->setParameter('organisation', $organisation);
        }

        if (null !== $status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $type) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function findResolvedTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', [Ticket::STATUS_RESOLVED])
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findClosedTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', [Ticket::STATUS_CLOSED])
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function closeResolvedTickets(int $days = 2): int
    {
        // get current date
        $today = Carbon::now('UTC');
        $today = $today->subDays($days);

        $sql  = 'UPDATE Ticket\Entity\Ticket t ';
        $sql .= 'SET t.status = 5,t.closeDate = :closed WHERE t.status = 4 AND t.resolveDate < :dateMin';
        return $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('closed', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->setParameter('dateMin', $today)
            ->execute();
    }

    public function findTicketsDueWithin(int $target, int $period): array
    {
        $date     = Carbon::now('UTC');
        $inFuture = clone $date;

        switch ($period) {
            case TicketService::DUE_PERIOD_MINUTES:
                $inFuture->addMinutes($target);
                break;
            case TicketService::DUE_PERIOD_HOURS:
                $inFuture->addHours($target);
                break;
            case TicketService::DUE_PERIOD_DAYS:
                $inFuture->addDays($target);
                break;
            case TicketService::DUE_PERIOD_WEEKS:
                $inFuture->addWeeks($target);
                break;
            case TicketService::DUE_PERIOD_MONTHS:
                $inFuture->addMonths($target);
                break;
        }

        $qb = $this->createQueryBuilder('q')
            ->select('t')
            ->from(Ticket::class, 't')
            ->andWhere('t.due_date BETWEEN :dateMin AND :dateMax')
            ->setParameter('dateMin', $date->format('Y-m-d H:i:s'))
            ->setParameter('dateMax', $inFuture->format('Y-m-d H:i:s'))
            ->andWhere('t.status <= 2');

        return $qb->getQuery()->getResult();
    }

    public function findOverdueTickets(): array
    {
        $now = Carbon::now('UTC');

        return $this->getEntityManager()->createQueryBuilder('q')
            ->select('t')
            ->from(Ticket::class, 't')
            ->andWhere('t.due_date < :today')
            ->andWhere('t.lastNotified < t.due_date')
            ->setParameter('today', $now->format('Y-m-d H:i:s'))
            ->andWhere('t.status <= 3')
            ->getQuery()
            ->getResult();
    }

    public function findWaitingTicketsToUpdateById(): array
    {
        $now = Carbon::now('UTC');

        $result = $this->getEntityManager()->createQueryBuilder('q')
            ->select('t.id')
            ->from(Ticket::class, 't')
            ->where('t.status = :t_status')
            ->andWhere('t.waitingResetDate < :date_now OR t.waitingResetDate is null')
            ->setParameter('date_now', $now->format('Y-m-d H:i:s'))
            ->andWhere('t.status = :t_status')
            ->setParameter('t_status', Status::STATUS_ON_HOLD)
            ->getQuery()
            ->getScalarResult();

        // as we only have one element per array we can use 'current' as a callback instead of a closure
        return array_map('current', $result);
    }

    public function findAgentStats(
        int $agentId,
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null
    ): array {
        $agent = $this->getEntityManager()->getRepository(Agent::class)->find($agentId);
        if ($agent === null) {
            throw new Exception('Agent not found');
        }

        $stats = [];

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Ticket::class, 't')
            ->where('t.agent = :agent')
            ->setParameter('agent', $agentId);

        if ($periodStart && $periodEnd !== null) {
            $qb->andWhere('t.createdAt BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $periodStart->format('Y-m-d H:i:s'))
                ->setParameter('dateMax', $periodEnd->format('Y-m-d H:i:s'));
        }

        $stats['open'] = $qb->getQuery()->getSingleScalarResult();

        /** @var Status[] $statusTypes */
        $statusTypes = $this->getEntityManager()->getRepository(Status::class)->findAll();

        foreach ($statusTypes as $statusType) {
            if ($statusType->getId() === Status::STATUS_OPEN) {
                continue;
            }

            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('COUNT(t.id)')
                ->from(TicketResponse::class, 't')
                ->where('t.agent = :agent')
                ->andWhere('t.ticket_status = :status')
                ->setParameter('agent', $agentId)
                ->setParameter('status', $statusType->getId());

            if ($periodStart && $periodEnd !== null) {
                $qb->andWhere('t.response_date BETWEEN :dateMin AND :dateMax')
                    ->setParameter('dateMin', $periodStart->format('Y-m-d H:i:s'))
                    ->setParameter('dateMax', $periodEnd->format('Y-m-d H:i:s'));
            }

            $stats[Status::getStatusTextFromId($statusType->getId())] = $qb->getQuery()->getSingleScalarResult();
        }

        return $stats;
    }

    public function findAllAgentStats(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null
    ): array {
        /** @var Agent[] $agents */
        $agents = $this->getEntityManager()->getRepository(Agent::class)->findBy([
            'status' => 1,
        ]);

        $stats = [];
        foreach ($agents as $agent) {
            $stats[$agent->getId()]         = $this->findAgentStats($agent->getId(), $periodStart, $periodEnd);
            $stats[$agent->getId()]['name'] = $agent->getFullName();
        }

        return $stats;
    }
}
