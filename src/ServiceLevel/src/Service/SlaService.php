<?php

declare(strict_types=1);

namespace ServiceLevel\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Priority;

class SlaService
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Save/update business hours information
     *
     * @param array $data form data
     * @return BusinessHours
     */
    public function saveBusinessHours(array $data): BusinessHours
    {
        // if id exists we're updating, otherwise creating
        $id = $data['id'] ?? null;
        if (empty($id)) {
            // new instance
            $businessHours = new BusinessHours();
        } else {
            // fetch existing data
            $businessHours = $this->findBusinessHoursById((int) $id);
        }

        // repopulate data
        $businessHours->exchangeArray($data);

        if (empty($id)) {
            // if we have no id then we are creating a new entity
            $this->entityManager->persist($businessHours);
        }

        // save
        $this->entityManager->flush();

        return $businessHours;
    }

    /**
     * Delete business hours entry
     */
    public function deleteBusinessHours(int $id): void
    {
        $businessHours = $this->findBusinessHoursById($id);
        $this->entityManager->remove($businessHours);
        $this->entityManager->flush();
    }

    /**
     * Find business hours from id
     */
    public function findBusinessHoursById(int $id): BusinessHours
    {
        return $this->entityManager->getRepository(BusinessHours::class)->find($id);
    }

    /**
     * Fetch list of business hours
     *
     * @return array
     */
    public function findAllBusinessHours(): array
    {
        return $this->entityManager
            ->createQueryBuilder('qb')
            ->select('b')
            ->from(BusinessHours::class, 'b')
            ->orderBy('b.name')
            ->getQuery()->getResult();
    }

    /**
     * Fetch list of SLA policies
     *
     * @return array
     */
    public function findAllSlaPolicies(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Sla::class, 's')
            ->orderBy('s.name')
            ->getQuery()->getResult();
    }

    /**
     * Find SLA by id
     *
     * @param int $id sla id
     * @return Sla|null null if not found
     */
    public function findSlaById(int $id): ?Sla
    {
        return $this->entityManager->getRepository(Sla::class)->find($id);
    }

    /**
     * Create/update SLA policy
     *
     * @param array $data
     * @return Sla
     * @throws Exception
     */
    public function createSla(array $data): Sla
    {
        $this->entityManager->clear();
        $id = (int) $data['id'] ?? null;
        if ($id !== 0) {
            // updating fetch sla and remove all targets
            $sla = $this->findSlaById($id);

            $this->deleteSlaTargets($sla);
        } else {
            $sla = new Sla();
        }

        $businessHoursId = (int) $data['business_hours'] ?? null;
        if ($businessHoursId === 0) {
            throw new Exception('Business hours id not passed');
        }

        $businessHours = $this->findBusinessHoursById($businessHoursId);
        if ($businessHours === null) {
            throw new Exception('Business Hours not found');
        }

        $sla->setName($data['name']);
        $sla->setBusinessHours($businessHours);

        $target = new SlaTarget();
        $target->setPriority($this->findPriorityById(Priority::PRIORITY_LOW));
        $target->setResponseTime($data['p_low_response_time']);
        $target->setResolveTime($data['p_low_resolve_time']);
        $target->setSla($sla);
        $sla->addSlaTarget($target);

        $target = new SlaTarget();
        $target->setPriority($this->findPriorityById(Priority::PRIORITY_MEDIUM));
        $target->setResponseTime($data['p_medium_response_time']);
        $target->setResolveTime($data['p_medium_resolve_time']);
        $target->setSla($sla);
        $sla->addSlaTarget($target);

        $target = new SlaTarget();
        $target->setPriority($this->findPriorityById(Priority::PRIORITY_HIGH));
        $target->setResponseTime($data['p_high_response_time']);
        $target->setResolveTime($data['p_high_resolve_time']);
        $target->setSla($sla);
        $sla->addSlaTarget($target);

        $target = new SlaTarget();
        $target->setPriority($this->findPriorityById(Priority::PRIORITY_URGENT));
        $target->setResponseTime($data['p_urgent_response_time']);
        $target->setResolveTime($data['p_urgent_resolve_time']);
        $target->setSla($sla);
        $sla->addSlaTarget($target);

        $target = new SlaTarget();
        $target->setPriority($this->findPriorityById(Priority::PRIORITY_CRITICAL));
        $target->setResponseTime($data['p_critical_response_time']);
        $target->setResolveTime($data['p_critical_resolve_time']);
        $target->setSla($sla);
        $sla->addSlaTarget($target);

        //$this->entityManager->persist($target);
        if ($id === 0) {
            $this->entityManager->persist($sla);
        }

        $this->entityManager->flush();

        return $sla;
    }

    /**
     * Fetch SLA target response/resolution times
     *
     * @param int $slaId id of corresponding SLA
     * @return array
     */
    public function findSlaTargetsBySlaId(int $slaId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(SlaTarget::class, 't')
            ->where('t.sla = :slaId')
            ->setParameter('slaId', $slaId)
            ->getQuery()->getResult();
    }

    /**
     * Delete sla targets
     *
     * @param Sla $sla sla to delete targets for
     */
    public function deleteSlaTargets(Sla $sla): void
    {
        // find original sla targets
        $targets = $this->findSlaTargetsBySlaId($sla->getId());

        // remove them
        foreach ($targets as $target) {
            $this->entityManager->remove($target);
            $this->entityManager->flush();
        }
    }

    /**
     * Find priority from database
     *
     * @param int $id id of priority
     * @return Priority
     */
    public function findPriorityById(int $id): Priority
    {
        return $this->entityManager->getRepository(Priority::class)->find($id);
    }
}
