<?php

declare(strict_types=1);

namespace Ticket\Repository;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Ticket\Entity\Ticket;

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
     * @return array ticket list
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('q');

        return $qb->select('t')
            ->from(Ticket::class, 't')
            ->orderBy('t.status')
            ->addOrderBy('t.priority')
            ->addOrderBy('t.start_date')
            ->getQuery()->getResult();
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
        // get query builder
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['t'])
            ->from(Ticket::class, 't')
            ->orderBy('t.status')
            ->addOrderBy('t.priority')
            ->addOrderBy('t.start_date');

        if (isset($options['queue_id'])) {
            $queueId = (int) $options['queue_id'];
            $qb->where('t.queue = :queue')
                ->setParameter('queue', $queueId);
        }

        if (isset($options['status_id'])) {
            $statusId = (int) $options['status_id'];
            $qb->where('t.status = :status')
                ->setParameter('status', $statusId);
        }

        // if organisation uuid is defined, grab that organisation only
        if (isset($options['organisation_uuid'])) {
            $organisationUuid = $options['organisation_uuid'];
            $qb->leftJoin('t.organisation', 'o')
                ->where('o.uuid = :uuid')
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
            ->addOrderBy('t.start_date');

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
            ->where('t.start_date BETWEEN :dateMin AND :dateMax')
            ->setParameter('dateMin', $today->format('Y-m-d 00:00:00'))
            ->setParameter('dateMax', $today->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->useQueryCache(true)
            ->getSingleScalarResult();
    }

    public function findOverdueTicketCount(): int
    {
        $today = new DateTime('now', new DateTimeZone('UTC'));

        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.start_date < :date')
            ->setParameter('date', $today->format('Y-m-d H:i:s'))
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

    public function findTotalTicketCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->useQueryCache(true)
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
}
