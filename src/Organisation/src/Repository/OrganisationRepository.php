<?php
declare(strict_types=1);

namespace Organisation\Repository;

use Organisation\Entity\Organisation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ramsey\Uuid\Uuid;

class OrganisationRepository extends EntityRepository
{
    public function findOneByUuid(string $uuid) : ?Organisation
    {
        $organisation = $this->getEntityManager()->createQueryBuilder()
            ->select('o')
            ->from(Organisation::class, 'o')
            ->where('o.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_OBJECT);

        if ($organisation instanceof Organisation) {
            return $organisation;
        }

        return null;
    }

    public function findAll(array $options = []): ?array
    {
        $qb = $this->createQueryBuilder('qb');

        $qb->select('o')
            ->from(Organisation::class, 'o')
            ->orderBy('o.name')
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getResult();
    }
}