<?php

declare(strict_types=1);

namespace OrganisationSite\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use OrganisationSite\Entity\SiteEntity;

class SiteRepository extends EntityRepository
{
    public function findAll()
    {
        return parent::findAll();
    }

    public function findByOrganisationId(int $id): array
    {
        $qb = $this->createQueryBuilder('cb')
            ->select('s')
            ->from(SiteEntity::class, 's')
            ->join('s.organisation', 'o')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->orderBy('o.name');

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }
}