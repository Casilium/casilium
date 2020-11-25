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
        if (empty($id)) {
            $businessHours = new BusinessHours();
        } else {
            $businessHours = $this->findBusinessHoursById((int) $id);
        }

        $businessHours->exchangeArray($data);

        if (empty($id)) {
            $this->entityManager->persist($businessHours);
        }

        $this->entityManager->flush();

        return $businessHours;
    }

    public function deleteBusinessHours(int $id): void
    {
        $businessHours = $this->findBusinessHoursById($id);
        $this->entityManager->remove($businessHours);
        $this->entityManager->flush();
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