<?php

declare(strict_types=1);

namespace TicketTest\Repository;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Organisation\Entity\Organisation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ServiceLevel\Entity\SlaTarget;
use Ticket\Entity\Agent;
use Ticket\Entity\Status;
use Ticket\Entity\Ticket;
use Ticket\Repository\TicketRepository;
use Ticket\Service\TicketService;

class TicketRepositoryTest extends TestCase
{
    private TicketRepository $repository;
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private Query $query;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder  = $this->createMock(QueryBuilder::class);
        $this->query         = $this->createMock(Query::class);

        // Create repository with mocked dependencies
        $this->repository = $this->getMockBuilder(TicketRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager', 'createQueryBuilder'])
            ->getMock();

        $this->repository->method('getEntityManager')->willReturn($this->entityManager);
        $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);

        // Set the protected _em property for methods that access it directly
        $reflection = new ReflectionClass($this->repository);
        $emProperty = $reflection->getProperty('_em');
        $emProperty->setAccessible(true);
        $emProperty->setValue($this->repository, $this->entityManager);
    }

    public function testFindTicketByUuid(): void
    {
        $uuid   = 'test-uuid-123';
        $ticket = $this->createMock(Ticket::class);

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->with('SELECT t FROM Ticket\Entity\Ticket t WHERE t.uuid = ?1')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('setParameter')
            ->with(1, $uuid)
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getSingleResult')
            ->willReturn($ticket);

        $result = $this->repository->findTicketByUuid($uuid);

        $this->assertSame($ticket, $result);
    }

    public function testFindTicketByUuidReturnsNull(): void
    {
        $uuid = 'non-existent-uuid';

        $this->entityManager->method('createQuery')->willReturn($this->query);
        $this->query->method('setParameter')->willReturn($this->query);
        $this->query->method('getSingleResult')->willReturn(null);

        $result = $this->repository->findTicketByUuid($uuid);

        $this->assertNull($result);
    }

    public function testSaveTicketWithOrganisationAndSlaTarget(): void
    {
        $ticket       = $this->createMock(Ticket::class);
        $organisation = $this->createMock(Organisation::class);
        $slaTarget    = $this->createMock(SlaTarget::class);
        $orgReference = $this->createMock(Organisation::class);
        $slaReference = $this->createMock(SlaTarget::class);

        $organisation->method('getId')->willReturn(123);
        $slaTarget->method('getId')->willReturn(456);

        $ticket->method('getOrganisation')->willReturn($organisation);
        $ticket->method('getSlaTarget')->willReturn($slaTarget);

        $referenceCalls = [
            [Organisation::class, 123, $orgReference],
            [SlaTarget::class, 456, $slaReference],
        ];
        $referenceIndex = 0;

        $this->entityManager->expects($this->exactly(2))
            ->method('getReference')
            ->willReturnCallback(function (string $class, int $id) use (&$referenceCalls, &$referenceIndex) {
                $this->assertSame($referenceCalls[$referenceIndex][0], $class);
                $this->assertSame($referenceCalls[$referenceIndex][1], $id);

                $result = $referenceCalls[$referenceIndex][2];
                $referenceIndex++;

                return $result;
            });

        $ticket->expects($this->once())
            ->method('setOrganisation')
            ->with($orgReference);

        $ticket->expects($this->once())
            ->method('setSlaTarget')
            ->with($slaReference);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($ticket);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->repository->save($ticket);

        $this->assertSame($ticket, $result);
    }

    public function testSaveTicketWithoutSlaTarget(): void
    {
        $ticket       = $this->createMock(Ticket::class);
        $organisation = $this->createMock(Organisation::class);
        $orgReference = $this->createMock(Organisation::class);

        $organisation->method('getId')->willReturn(123);

        $ticket->method('getOrganisation')->willReturn($organisation);
        $ticket->method('getSlaTarget')->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('getReference')
            ->with(Organisation::class, 123)
            ->willReturn($orgReference);

        $ticket->expects($this->once())
            ->method('setOrganisation')
            ->with($orgReference);

        $ticket->expects($this->never())
            ->method('setSlaTarget');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($ticket);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->repository->save($ticket);

        $this->assertSame($ticket, $result);
    }

    public function testFindRecentTicketsByContact(): void
    {
        $contactId = 789;
        $limit     = 3;
        $tickets   = [
            $this->createMock(Ticket::class),
            $this->createMock(Ticket::class),
        ];

        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->with('SELECT t FROM Ticket\Entity\Ticket t where t.contact = ?1 ORDER BY t.id DESC')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('setParameter')
            ->with(1, $contactId)
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('setMaxResults')
            ->with($limit)
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($tickets);

        $result = $this->repository->findRecentTicketsByContact($contactId, $limit);

        $this->assertSame($tickets, $result);
    }

    public function testFindRecentTicketsByContactWithDefaultLimit(): void
    {
        $contactId = 789;
        $tickets   = [$this->createMock(Ticket::class)];

        $this->entityManager->method('createQuery')->willReturn($this->query);
        $this->query->method('setParameter')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('setMaxResults')
            ->with(5) // Default limit
            ->willReturn($this->query);
        $this->query->method('getResult')->willReturn($tickets);

        $result = $this->repository->findRecentTicketsByContact($contactId);

        $this->assertSame($tickets, $result);
    }

    public function testFindUnresolvedTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('42');

        $result = $this->repository->findUnresolvedTicketCount();

        $this->assertEquals(42, $result);
    }

    public function testFindDueTodayTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('15');

        $result = $this->repository->findDueTodayTicketCount();

        $this->assertEquals(15, $result);
    }

    public function testFindOverdueTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('8');

        $result = $this->repository->findOverdueTicketCount();

        $this->assertEquals(8, $result);
    }

    public function testFindOpenTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('25');

        $result = $this->repository->findOpenTicketCount();

        $this->assertEquals(25, $result);
    }

    public function testFindOnHoldTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('10');

        $result = $this->repository->findOnHoldTicketCount();

        $this->assertEquals(10, $result);
    }

    public function testFindTotalTicketCountWithoutOptions(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('100');

        $result = $this->repository->findTotalTicketCount();

        $this->assertEquals(100, $result);
    }

    public function testFindTotalTicketCountWithDateRange(): void
    {
        $options = [
            'start' => '2023-01-01',
            'end'   => '2023-01-31',
        ];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('45');

        $result = $this->repository->findTotalTicketCount($options);

        $this->assertEquals(45, $result);
    }

    public function testFindTotalTicketCountWithOrganisation(): void
    {
        $options = [
            'organisation' => 123,
        ];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('30');

        $result = $this->repository->findTotalTicketCount($options);

        $this->assertEquals(30, $result);
    }

    public function testFindTicketCountWithAllOptions(): void
    {
        $options = [
            'start'        => '2023-01-01',
            'end'          => '2023-01-31',
            'organisation' => 123,
            'status'       => 2,
            'type'         => 1,
        ];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('12');

        $result = $this->repository->findTicketCount($options);

        $this->assertEquals(12, $result);
    }

    public function testFindResolvedTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('75');

        $result = $this->repository->findResolvedTicketCount();

        $this->assertEquals(75, $result);
    }

    public function testFindClosedTicketCount(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('useQueryCache')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('50');

        $result = $this->repository->findClosedTicketCount();

        $this->assertEquals(50, $result);
    }

    public function testCloseResolvedTicketsWithDefaultDays(): void
    {
        $this->entityManager->expects($this->once())
            ->method('createQuery')
            ->with($this->stringContains('UPDATE Ticket\Entity\Ticket t'))
            ->willReturn($this->query);

        $this->query->method('setParameter')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn(5); // 5 tickets updated

        $result = $this->repository->closeResolvedTickets();

        $this->assertEquals(5, $result);
    }

    public function testCloseResolvedTicketsWithCustomDays(): void
    {
        $days = 7;

        $this->entityManager->method('createQuery')->willReturn($this->query);
        $this->query->method('setParameter')->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn(3);

        $result = $this->repository->closeResolvedTickets($days);

        $this->assertEquals(3, $result);
    }

    public function testFindTicketsDueWithinMinutes(): void
    {
        $target  = 30;
        $period  = TicketService::DUE_PERIOD_MINUTES;
        $tickets = [$this->createMock(Ticket::class)];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($tickets);

        $result = $this->repository->findTicketsDueWithin($target, $period);

        $this->assertSame($tickets, $result);
    }

    public function testFindTicketsDueWithinHours(): void
    {
        $target  = 2;
        $period  = TicketService::DUE_PERIOD_HOURS;
        $tickets = [$this->createMock(Ticket::class)];

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('getResult')->willReturn($tickets);

        $result = $this->repository->findTicketsDueWithin($target, $period);

        $this->assertSame($tickets, $result);
    }

    public function testFindTicketsDueWithinDays(): void
    {
        $target = 3;
        $period = TicketService::DUE_PERIOD_DAYS;

        $this->setupBasicQueryBuilderMethods();
        $this->query->method('getResult')->willReturn([]);

        $result = $this->repository->findTicketsDueWithin($target, $period);

        $this->assertEquals([], $result);
    }

    public function testFindTicketsDueWithinWeeks(): void
    {
        $target = 1;
        $period = TicketService::DUE_PERIOD_WEEKS;

        $this->setupBasicQueryBuilderMethods();
        $this->query->method('getResult')->willReturn([]);

        $result = $this->repository->findTicketsDueWithin($target, $period);

        $this->assertEquals([], $result);
    }

    public function testFindTicketsDueWithinMonths(): void
    {
        $target = 1;
        $period = TicketService::DUE_PERIOD_MONTHS;

        $this->setupBasicQueryBuilderMethods();
        $this->query->method('getResult')->willReturn([]);

        $result = $this->repository->findTicketsDueWithin($target, $period);

        $this->assertEquals([], $result);
    }

    public function testFindOverdueTickets(): void
    {
        $tickets = [
            $this->createMock(Ticket::class),
            $this->createMock(Ticket::class),
        ];

        $qb = $this->createMock(QueryBuilder::class);
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->with('q')
            ->willReturn($qb);

        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);
        $qb->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($tickets);

        $result = $this->repository->findOverdueTickets();

        $this->assertSame($tickets, $result);
    }

    public function testFindWaitingTicketsToUpdateById(): void
    {
        $ticketIds   = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $expectedIds = [1, 2, 3];

        $qb = $this->createMock(QueryBuilder::class);
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->with('q')
            ->willReturn($qb);

        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);
        $qb->method('where')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);
        $qb->method('getQuery')->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getScalarResult')
            ->willReturn($ticketIds);

        $result = $this->repository->findWaitingTicketsToUpdateById();

        $this->assertEquals($expectedIds, $result);
    }

    public function testFindAgentStatsWithValidAgent(): void
    {
        $agentId     = 123;
        $agent       = $this->createMock(Agent::class);
        $periodStart = Carbon::parse('2023-01-01');
        $periodEnd   = Carbon::parse('2023-01-31');

        $agentRepo  = $this->createMock(EntityRepository::class);
        $statusRepo = $this->createMock(EntityRepository::class);

        $repoCalls = [
            [Agent::class, $agentRepo],
            [Status::class, $statusRepo],
        ];
        $repoIndex = 0;

        $this->entityManager->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnCallback(function (string $class) use (&$repoCalls, &$repoIndex) {
                $this->assertSame($repoCalls[$repoIndex][0], $class);

                $result = $repoCalls[$repoIndex][1];
                $repoIndex++;

                return $result;
            });

        $agentRepo->expects($this->once())
            ->method('find')
            ->with($agentId)
            ->willReturn($agent);

        $status = $this->createMock(Status::class);
        $status->method('getId')->willReturn(2);
        $statusRepo->method('findAll')->willReturn([$status]);

        $qb = $this->createMock(QueryBuilder::class);
        $this->entityManager->method('createQueryBuilder')->willReturn($qb);
        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);
        $qb->method('where')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);
        $qb->method('getQuery')->willReturn($this->query);

        $this->query->method('getSingleScalarResult')->willReturn(5);

        $result = $this->repository->findAgentStats($agentId, $periodStart, $periodEnd);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('open', $result);
    }

    public function testFindAgentStatsWithInvalidAgent(): void
    {
        $agentId = 999;

        $agentRepo = $this->createMock(EntityRepository::class);
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Agent::class)
            ->willReturn($agentRepo);

        $agentRepo->expects($this->once())
            ->method('find')
            ->with($agentId)
            ->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Agent not found');

        $this->repository->findAgentStats($agentId);
    }

    public function testFindAllAgentStats(): void
    {
        $agent1 = $this->createMock(Agent::class);
        $agent1->method('getId')->willReturn(1);
        $agent1->method('getFullName')->willReturn('John Doe');

        $agent2 = $this->createMock(Agent::class);
        $agent2->method('getId')->willReturn(2);
        $agent2->method('getFullName')->willReturn('Jane Smith');

        $agentRepo = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->willReturn($agentRepo);

        $agentRepo->expects($this->once())
            ->method('findBy')
            ->with(['status' => 1])
            ->willReturn([$agent1, $agent2]);

        // Mock the repository to avoid calling actual findAgentStats
        $mockRepo = $this->getMockBuilder(TicketRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findAgentStats', 'getEntityManager'])
            ->getMock();

        $mockRepo->method('getEntityManager')->willReturn($this->entityManager);

        $mockRepo->expects($this->exactly(2))
            ->method('findAgentStats')
            ->willReturnMap([
                [1, null, null, ['open' => 5]],
                [2, null, null, ['open' => 3]],
            ]);

        $result = $mockRepo->findAllAgentStats();

        $this->assertIsArray($result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(2, $result);
        $this->assertEquals('John Doe', $result[1]['name']);
        $this->assertEquals('Jane Smith', $result[2]['name']);
    }

    private function setupBasicQueryBuilderMethods(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('andWhere')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
    }
}
