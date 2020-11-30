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
        $sql   = 'SELECT c FROM OrganisationContact\Entity\Contact c where c.organisation = :org';
        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('org', $id);


        return $query->getResult();
    }
}
