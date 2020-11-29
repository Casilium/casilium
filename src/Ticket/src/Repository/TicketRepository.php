<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
use Ticket\Entity\Ticket;

class TicketRepository extends EntityRepository
{
    public function findTicketByUuid(string $uuid): ?Ticket
    {
        return $this->getEntityManager()
            ->createQuery('SELECT t FROM Ticket\Entity\Ticket t WHERE t.uuid = ?1')
            ->setParameter(1, $uuid)
            ->getSingleResult();
    }

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
