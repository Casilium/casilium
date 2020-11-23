<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
use Ticket\Entity\Queue;

class QueueRepository extends EntityRepository
{
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('qb');
        return $qb->select('q')
            ->from(Queue::class, 'q')
            ->orderBy('q.name')
            ->getQuery()
            ->getResult();
    }
}
