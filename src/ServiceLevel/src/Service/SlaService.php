<?php

declare(strict_types=1);

namespace ServiceLevel\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Organisation\Entity\Organisation;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Priority;

class SlaService
{
    /** Form field prefix carrying each priority's response and resolve times */
    private const TARGET_FIELDS = [
        Priority::PRIORITY_LOW      => 'p_low',
        Priority::PRIORITY_MEDIUM   => 'p_medium',
        Priority::PRIORITY_HIGH     => 'p_high',
        Priority::PRIORITY_URGENT   => 'p_urgent',
        Priority::PRIORITY_CRITICAL => 'p_critical',
    ];

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
            if ($businessHours === null) {
                throw new Exception('Business Hours not found');
            }
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
        if ($businessHours === null) {
            throw new Exception('Business Hours not found');
        }
        $this->entityManager->remove($businessHours);
        $this->entityManager->flush();
    }

    /**
     * Find business hours from id
     */
    public function findBusinessHoursById(int $id): ?BusinessHours
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
            ->createQueryBuilder()
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
     * @throws Exception
     */
    public function createSla(array $data): Sla
    {
        $this->entityManager->clear();
        $id = (int) $data['id'] ?? null;
        if ($id !== 0) {
            $sla = $this->findSlaById($id);
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

        if ($id === 0) {
            $this->entityManager->persist($sla);
        }

        $this->applySlaTargets($sla, $data);

        $this->entityManager->flush();

        return $sla;
    }

    /**
     * Set the response and resolve times for each priority.
     *
     * Targets are updated in place. Replacing them hands each one a new id and
     * leaves every ticket referencing the old one pointing at a row that no
     * longer exists, and ticket.sla_target_id has no foreign key to catch it.
     *
     * @param array $data submitted SLA form values
     */
    private function applySlaTargets(Sla $sla, array $data): void
    {
        $existing = [];
        foreach ($sla->getSlaTargets() ?? [] as $target) {
            $existing[$target->getPriority()->getId()] = $target;
        }

        foreach (self::TARGET_FIELDS as $priorityId => $prefix) {
            $target = $existing[$priorityId] ?? null;

            if ($target === null) {
                $target = new SlaTarget();
                $target->setPriority($this->findPriorityById($priorityId));
                $target->setSla($sla);
                $sla->addSlaTarget($target);
                $this->entityManager->persist($target);
            }

            $target->setResponseTime($data[$prefix . '_response_time']);
            $target->setResolveTime($data[$prefix . '_resolve_time']);
        }
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
     */
    public function findPriorityById(int $id): Priority
    {
        return $this->entityManager->getRepository(Priority::class)->find($id);
    }

    public function assignOrganisationSla(int $orgId, int $slaId): void
    {
        if ($orgId === 0) {
            throw new Exception('Client ID not passed');
        }

        if ($slaId === 0) {
            throw new Exception('Invalid SLA ID');
        }

        /** @var Organisation $organisation */
        $organisation = $this->entityManager->getRepository(Organisation::class)->find($orgId);

        /** @var Sla $sla */
        $sla = $this->findSlaById($slaId);
        $organisation->setSla($sla);
        $this->entityManager->flush();
    }
}
