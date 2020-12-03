<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Ticket\Entity\Ticket;
use Ticket\Service\TicketService;
use function sprintf;

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
        $sql   = 'SELECT t FROM Ticket\Entity\Ticket t where t.contact = ?1';
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
        $today = new DateTime('now', new DateTimeZone('UTC'));

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
        $start = isset($options['start']) ? Carbon::parse($options['start'], 'UTC') : null;
        $end   = isset($options['end']) ? Carbon::parse($options['end'], 'UTC') : null;

        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        if ($start !== null && $end !== null) {
            $qb->where('t.createdAt BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $start->format('Y-m-d 00:00:00'))
                ->setParameter('dateMax', $end->format('Y-m-d 23:59:59'));
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

        // add 2 days
        $today = $today->subDays($days);

        return $this->createQueryBuilder('t')
            ->update(Ticket::class, 't')
            ->set('t.status', 5)
            ->set('t.dateClosed', Carbon::now('UTC')->format('Y-m-d H:i:s'))
            ->where('t.resolveDate < :dateMin')
            ->setParameter('dateMin', $today->format('Y-m-d 00:00:00'))
            ->andWhere('t.status = :status')
            ->setParameter('status', Ticket::STATUS_RESOLVED)
            ->getQuery()->execute();
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
            ->andWhere('t.status <= 3');

        return $qb->getQuery()->getResult();
    }
}
