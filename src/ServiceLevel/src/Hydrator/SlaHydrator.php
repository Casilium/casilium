<?php

declare(strict_types=1);

namespace ServiceLevel\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use ServiceLevel\Service\SlaService;
use Ticket\Entity\Priority;

class SlaHydrator extends AbstractHydrator
{
    /** @var SlaService */
    protected $slaService;

    public function __construct(SlaService $slaService)
    {
        $this->slaService = $slaService;
    }

    /**
     * @param array $data data to populate
     * @param Sla|object $object
     * @return object|void
     */
    public function hydrate(array $data, object $object)
    {
        $object->setId((int) $data['id'] ?? null);
        $object->setName($data['name']);

        $businessHours = $this->slaService->findBusinessHoursById((int) $data['business_hours']);
        $object->setBusinessHours($businessHours);

        $slaTarget = new SlaTarget();
        $slaTarget->setPriority($this->slaService->findPriorityById(Priority::PRIORITY_LOW));
        $slaTarget->setResolveTime($data['p_low_response_time']);
        $slaTarget->setResolveTime($data['p_low_resolve_time']);
        $slaTarget->setSla($object);
        $object->addSlaTarget($slaTarget);

        $slaTarget = new SlaTarget();
        $slaTarget->setPriority($this->slaService->findPriorityById(Priority::PRIORITY_LOW));
        $slaTarget->setResolveTime($data['p_medium_response_time']);
        $slaTarget->setResolveTime($data['p_medium_resolve_time']);
        $object->addSlaTarget($slaTarget);

        $slaTarget = new SlaTarget();
        $slaTarget->setPriority($this->slaService->findPriorityById(Priority::PRIORITY_LOW));
        $slaTarget->setResolveTime($data['p_high_response_time']);
        $slaTarget->setResolveTime($data['p_high_resolve_time']);
        $object->addSlaTarget($slaTarget);

        $slaTarget = new SlaTarget();
        $slaTarget->setPriority($this->slaService->findPriorityById(Priority::PRIORITY_LOW));
        $slaTarget->setResolveTime($data['p_urgent_response_time']);
        $slaTarget->setResolveTime($data['p_urgent_resolve_time']);
        $object->addSlaTarget($slaTarget);

        $slaTarget = new SlaTarget();
        $slaTarget->setPriority($this->slaService->findPriorityById(Priority::PRIORITY_LOW));
        $slaTarget->setResolveTime($data['p_critical_response_time']);
        $slaTarget->setResolveTime($data['p_critical_resolve_time']);
        $object->addSlaTarget($slaTarget);

        return $object;
    }

    /**
     * @param Sla|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        if ($object->getId() === null) {
            return [];
        }

        /** @var SlaTarget[] $slaTargets */
        $slaTargets = $object->getSlaTarget();

        $targets = [];
        foreach ($slaTargets as $slaTarget) {
            $targets[$slaTarget->getPriority()->getId()] = $slaTarget;
        }

        return [
            'id'                       => $object->getId(),
            'name'                     => $object->getName(),
            'business_hours'           => $object->getBusinessHours()->getId(),
            'p_low_response_time'      => $targets[Priority::PRIORITY_LOW]->getResponseTime(),
            'p_low_resolve_time'       => $targets[Priority::PRIORITY_LOW]->getResolveTime(),
            'p_medium_response_time'   => $targets[Priority::PRIORITY_MEDIUM]->getResponseTime(),
            'p_medium_resolve_time'    => $targets[Priority::PRIORITY_MEDIUM]->getResolveTime(),
            'p_high_response_time'     => $targets[Priority::PRIORITY_HIGH]->getResponseTime(),
            'p_high_resolve_time'      => $targets[Priority::PRIORITY_HIGH]->getResolveTime(),
            'p_urgent_response_time'   => $targets[Priority::PRIORITY_URGENT]->getResponseTime(),
            'p_urgent_resolve_time'    => $targets[Priority::PRIORITY_URGENT]->getResolveTime(),
            'p_critical_response_time' => $targets[Priority::PRIORITY_CRITICAL]->getResponseTime(),
            'p_critical_resolve_time'  => $targets[Priority::PRIORITY_CRITICAL]->getResolveTime(),
        ];
    }
}
