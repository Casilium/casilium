<?php

declare(strict_types=1);

namespace ServiceLevel\Service;

use Doctrine\ORM\EntityManagerInterface;
use ServiceLevel\Entity\BusinessHours;

class SlaService
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveBusinessHours(array $data): BusinessHours
    {
        $id = $data['id'] ?? null;
        if (null === $id) {
            $businessHours = new BusinessHours();
        } else {
            $businessHours = $this->findBusinessHoursById((int) $id);
        }

        $businessHours->exchangeArray($data);

        if (null === $id) {
            $this->entityManager->persist($businessHours);
        }

        $this->entityManager->flush();

        return $businessHours;
    }

    public function findBusinessHoursById(int $id): BusinessHours
    {
        return $this->entityManager->getRepository(BusinessHours::class)->find($id);
    }

    public function findAllBusinessHours(): array
    {
        return $this->entityManager
            ->createQueryBuilder('qb')
            ->select('b')
            ->from(BusinessHours::class, 'b')
            ->orderBy('b.name')
            ->getQuery()->getResult();
    }
}