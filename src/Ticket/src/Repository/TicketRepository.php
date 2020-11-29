<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ticket\Entity\Ticket;
use function sprintf;

class TicketRepository extends EntityRepository
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
}
