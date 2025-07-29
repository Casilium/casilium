<?php

declare(strict_types=1);

namespace ServiceLevelTest\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Organisation\Entity\Organisation;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ServiceLevel\Entity\BusinessHours;
use ServiceLevel\Entity\Sla;
use ServiceLevel\Entity\SlaTarget;
use ServiceLevel\Service\SlaService;
use Ticket\Entity\Priority;

class SlaServiceTest extends TestCase
{
    use ProphecyTrait;

    private SlaService $slaService;
    private ObjectProphecy $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->slaService    = new SlaService($this->entityManager->reveal());
    }

    public function testSaveBusinessHoursUpdatesExistingWhenIdProvided(): void
    {
        $data = [
            'id'       => 123,
            'name'     => 'Updated Business Hours',
            'timezone' => 'Europe/London',
        ];

        $existingBusinessHours = $this->createMock(BusinessHours::class);
        $repository            = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(BusinessHours::class)->willReturn($repository->reveal());
        $repository->find(123)->willReturn($existingBusinessHours);

        $existingBusinessHours->expects($this->once())->method('exchangeArray')->with($data);
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->slaService->saveBusinessHours($data);

        $this->assertSame($existingBusinessHours, $result);
    }

    public function testDeleteBusinessHours(): void
    {
        $id            = 456;
        $businessHours = $this->createMock(BusinessHours::class);
        $repository    = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(BusinessHours::class)->willReturn($repository->reveal());
        $repository->find($id)->willReturn($businessHours);

        $this->entityManager->remove($businessHours)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->slaService->deleteBusinessHours($id);
    }

    public function testFindBusinessHoursById(): void
    {
        $id            = 789;
        $businessHours = $this->createMock(BusinessHours::class);
        $repository    = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(BusinessHours::class)->willReturn($repository->reveal());
        $repository->find($id)->willReturn($businessHours);

        $result = $this->slaService->findBusinessHoursById($id);

        $this->assertSame($businessHours, $result);
    }

    public function testFindAllBusinessHours(): void
    {
        $businessHoursList = [$this->createMock(BusinessHours::class)];
        $queryBuilder      = $this->prophesize(QueryBuilder::class);
        $query             = $this->prophesize(Query::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('b')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(BusinessHours::class, 'b')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('b.name')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getResult()->willReturn($businessHoursList);

        $result = $this->slaService->findAllBusinessHours();

        $this->assertSame($businessHoursList, $result);
    }

    public function testFindAllSlaPolicies(): void
    {
        $slaPolicies  = [$this->createMock(Sla::class)];
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query        = $this->prophesize(Query::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('s')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Sla::class, 's')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('s.name')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getResult()->willReturn($slaPolicies);

        $result = $this->slaService->findAllSlaPolicies();

        $this->assertSame($slaPolicies, $result);
    }

    public function testFindSlaById(): void
    {
        $id         = 101;
        $sla        = $this->createMock(Sla::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Sla::class)->willReturn($repository->reveal());
        $repository->find($id)->willReturn($sla);

        $result = $this->slaService->findSlaById($id);

        $this->assertSame($sla, $result);
    }

    public function testCreateSlaWithNewSla(): void
    {
        $data = [
            'id'                       => 0, // New SLA
            'name'                     => 'Test SLA',
            'business_hours'           => 10,
            'p_low_response_time'      => '04:00',
            'p_low_resolve_time'       => '24:00',
            'p_medium_response_time'   => '02:00',
            'p_medium_resolve_time'    => '16:00',
            'p_high_response_time'     => '01:00',
            'p_high_resolve_time'      => '08:00',
            'p_urgent_response_time'   => '00:30',
            'p_urgent_resolve_time'    => '04:00',
            'p_critical_response_time' => '00:15',
            'p_critical_resolve_time'  => '02:00',
        ];

        $businessHours     = $this->createMock(BusinessHours::class);
        $businessHoursRepo = $this->prophesize(EntityRepository::class);
        $priorityRepo      = $this->prophesize(EntityRepository::class);

        // Mock priorities
        $lowPriority      = $this->createMock(Priority::class);
        $mediumPriority   = $this->createMock(Priority::class);
        $highPriority     = $this->createMock(Priority::class);
        $urgentPriority   = $this->createMock(Priority::class);
        $criticalPriority = $this->createMock(Priority::class);

        $this->entityManager->clear()->shouldBeCalled();
        $this->entityManager->getRepository(BusinessHours::class)->willReturn($businessHoursRepo->reveal());
        $this->entityManager->getRepository(Priority::class)->willReturn($priorityRepo->reveal());

        $businessHoursRepo->find(10)->willReturn($businessHours);
        $priorityRepo->find(Priority::PRIORITY_LOW)->willReturn($lowPriority);
        $priorityRepo->find(Priority::PRIORITY_MEDIUM)->willReturn($mediumPriority);
        $priorityRepo->find(Priority::PRIORITY_HIGH)->willReturn($highPriority);
        $priorityRepo->find(Priority::PRIORITY_URGENT)->willReturn($urgentPriority);
        $priorityRepo->find(Priority::PRIORITY_CRITICAL)->willReturn($criticalPriority);

        $this->entityManager->persist(Argument::type(Sla::class))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->slaService->createSla($data);

        $this->assertInstanceOf(Sla::class, $result);
    }

    public function testCreateSlaThrowsExceptionWhenBusinessHoursIdMissing(): void
    {
        $data = [
            'id'             => 0,
            'name'           => 'Test SLA',
            'business_hours' => 0, // Invalid business hours ID
        ];

        $this->entityManager->clear()->shouldBeCalled();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Business hours id not passed');

        $this->slaService->createSla($data);
    }

    public function testFindSlaTargetsBySlaId(): void
    {
        $slaId        = 123;
        $targets      = [$this->createMock(SlaTarget::class)];
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query        = $this->prophesize(Query::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('t')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(SlaTarget::class, 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('t.sla = :slaId')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('slaId', $slaId)->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getResult()->willReturn($targets);

        $result = $this->slaService->findSlaTargetsBySlaId($slaId);

        $this->assertSame($targets, $result);
    }

    public function testDeleteSlaTargets(): void
    {
        $sla = $this->createMock(Sla::class);
        $sla->method('getId')->willReturn(456);

        $target  = $this->createMock(SlaTarget::class);
        $targets = [$target];

        // Mock the query for finding targets
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query        = $this->prophesize(Query::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('t')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(SlaTarget::class, 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('t.sla = :slaId')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('slaId', 456)->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getResult()->willReturn($targets);

        $this->entityManager->remove($target)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->slaService->deleteSlaTargets($sla);
    }

    public function testFindPriorityById(): void
    {
        $id         = 2;
        $priority   = $this->createMock(Priority::class);
        $repository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Priority::class)->willReturn($repository->reveal());
        $repository->find($id)->willReturn($priority);

        $result = $this->slaService->findPriorityById($id);

        $this->assertSame($priority, $result);
    }

    public function testAssignOrganisationSla(): void
    {
        $orgId = 123;
        $slaId = 456;

        $organisation = $this->createMock(Organisation::class);
        $sla          = $this->createMock(Sla::class);

        $orgRepo = $this->prophesize(EntityRepository::class);
        $slaRepo = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(Organisation::class)->willReturn($orgRepo->reveal());
        $this->entityManager->getRepository(Sla::class)->willReturn($slaRepo->reveal());

        $orgRepo->find($orgId)->willReturn($organisation);
        $slaRepo->find($slaId)->willReturn($sla);

        $organisation->expects($this->once())->method('setSla')->with($sla)->willReturn($organisation);
        $this->entityManager->flush()->shouldBeCalled();

        $this->slaService->assignOrganisationSla($orgId, $slaId);
    }

    public function testAssignOrganisationSlaThrowsExceptionForInvalidOrgId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Client ID not passed');

        $this->slaService->assignOrganisationSla(0, 123);
    }

    public function testAssignOrganisationSlaThrowsExceptionForInvalidSlaId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid SLA ID');

        $this->slaService->assignOrganisationSla(123, 0);
    }

    public function testCreateSlaWithExistingSla(): void
    {
        $data = [
            'id'                       => 999, // Existing SLA
            'name'                     => 'Updated SLA',
            'business_hours'           => 10,
            'p_low_response_time'      => '04:00',
            'p_low_resolve_time'       => '24:00',
            'p_medium_response_time'   => '02:00',
            'p_medium_resolve_time'    => '16:00',
            'p_high_response_time'     => '01:00',
            'p_high_resolve_time'      => '08:00',
            'p_urgent_response_time'   => '00:30',
            'p_urgent_resolve_time'    => '04:00',
            'p_critical_response_time' => '00:15',
            'p_critical_resolve_time'  => '02:00',
        ];

        $existingSla = $this->createMock(Sla::class);
        $existingSla->method('getId')->willReturn(999);

        $businessHours     = $this->createMock(BusinessHours::class);
        $businessHoursRepo = $this->prophesize(EntityRepository::class);
        $slaRepo           = $this->prophesize(EntityRepository::class);
        $priorityRepo      = $this->prophesize(EntityRepository::class);

        // Mock priorities
        $lowPriority      = $this->createMock(Priority::class);
        $mediumPriority   = $this->createMock(Priority::class);
        $highPriority     = $this->createMock(Priority::class);
        $urgentPriority   = $this->createMock(Priority::class);
        $criticalPriority = $this->createMock(Priority::class);

        $this->entityManager->clear()->shouldBeCalled();
        $this->entityManager->getRepository(Sla::class)->willReturn($slaRepo->reveal());
        $this->entityManager->getRepository(BusinessHours::class)->willReturn($businessHoursRepo->reveal());
        $this->entityManager->getRepository(Priority::class)->willReturn($priorityRepo->reveal());

        $slaRepo->find(999)->willReturn($existingSla);
        $businessHoursRepo->find(10)->willReturn($businessHours);
        $priorityRepo->find(Priority::PRIORITY_LOW)->willReturn($lowPriority);
        $priorityRepo->find(Priority::PRIORITY_MEDIUM)->willReturn($mediumPriority);
        $priorityRepo->find(Priority::PRIORITY_HIGH)->willReturn($highPriority);
        $priorityRepo->find(Priority::PRIORITY_URGENT)->willReturn($urgentPriority);
        $priorityRepo->find(Priority::PRIORITY_CRITICAL)->willReturn($criticalPriority);

        // Mock the deleteTargets call (simplified - it would call the query builder)
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query        = $this->prophesize(Query::class);
        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('t')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(SlaTarget::class, 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('t.sla = :slaId')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('slaId', 999)->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());
        $query->getResult()->willReturn([]); // No existing targets

        $existingSla->expects($this->once())->method('setName')->with('Updated SLA')->willReturn($existingSla);
        $existingSla->expects($this->once())
            ->method('setBusinessHours')
            ->with($businessHours)
            ->willReturn($existingSla);
        $existingSla->expects($this->exactly(5))->method('addSlaTarget')->with($this->isInstanceOf(SlaTarget::class));

        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->slaService->createSla($data);

        $this->assertSame($existingSla, $result);
    }
}
