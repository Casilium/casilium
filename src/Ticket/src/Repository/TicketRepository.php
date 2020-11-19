<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Organisation\Entity\Organisation;
use Ticket\Entity\Ticket;

class TicketRepository extends EntityRepository
{
    /**
     * @param string $uuid
     * @return Ticket
     */
    public function findTicketByUuid(string $uuid): Ticket
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function save(Ticket $ticket): Ticket
    {
        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
        return $ticket;
    }

    public function findRecentTicketsByContact(int $contactId): array
    {
        $qb = $this->createQueryBuilder('qb');

        $qb->select('t')
            ->from(Ticket::class, 't')
            ->where('t.contact = :contact_id')
            ->orderBy('t.id', 'DESC')
            ->setParameter('contact_id', $contactId)
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getResult();

    }

    public function findAll() {
        $qb = $this->createQueryBuilder('q');
        return $qb->select('t')
            ->from(Ticket::class, 't')
            ->orderBy('t.priority')
            ->addOrderBy('t.start_date')
            ->getQuery()->getResult();
    }

    public function findByOrganisationUuid(string $uuid)
    {
        $qb = $this->createQueryBuilder('q');

        $qb->select('t')
            ->from(Ticket::class, 't')
            ->where('o.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->leftJoin('t.organisation', 'o')
            ->orderBy('t.priority')
            ->addOrderBy('t.start_date');

        return $qb->getQuery()->getResult();
    }

}
