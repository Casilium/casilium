<?php

declare(strict_types=1);

namespace Ticket\Repository;

use Doctrine\ORM\EntityRepository;
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
}
