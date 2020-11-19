<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
use Ticket\Entity\TicketResponse;

class TicketResponseRepository extends EntityRepository
{
    public function findTicketResponsesByTicketId(int $ticketId): array
    {
        $qb = $this->createQueryBuilder('qb');

        $qb->select('r')
            ->from(TicketResponse::class, 'r')
            ->where('r.ticket = :id')
            ->orderBy('r.id', 'ASC')
            ->setParameter('id', $ticketId)
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getResult();

    }
}
