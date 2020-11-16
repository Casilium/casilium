<?php

declare(strict_types=1);

namespace OrganisationContact\Repository;

use Doctrine\ORM\EntityRepository;
use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;

class ContactRepository extends EntityRepository
{
    public function findByCorporationId(int $id): ?array
    {
        $qb = $this->createQueryBuilder('qb');

        $qb->select('c')
            ->from(Contact::class, 'c')
            ->join(Organisation::class, 'o')
            ->where('c.organisation = :id')
            ->orderBy('c.first_name')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getResult();
    }
}