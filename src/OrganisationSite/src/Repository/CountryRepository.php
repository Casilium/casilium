<?php

declare(strict_types=1);

namespace OrganisationSite\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use OrganisationSite\Entity\CountryEntity;

class CountryRepository extends EntityRepository
{
    public function findAll(array $options = []): ?array
    {
        $result = $this->createQueryBuilder('c')->getQuery();
        return $result->getResult(Query::HYDRATE_OBJECT);
    }

}