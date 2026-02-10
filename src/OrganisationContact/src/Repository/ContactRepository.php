<?php

declare(strict_types=1);

namespace OrganisationContact\Repository;

use Doctrine\ORM\EntityRepository;

class ContactRepository extends EntityRepository
{
    public function findByCorporationId(int $id, bool $activeOnly = false): ?array
    {
        $sql = 'SELECT c FROM OrganisationContact\Entity\Contact c WHERE c.organisation = :org';

        if ($activeOnly) {
            $sql .= ' AND c.isActive = true';
        }

        $sql  .= ' ORDER BY c.firstName, c.lastName';
        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('org', $id);

        return $query->getResult();
    }
}
