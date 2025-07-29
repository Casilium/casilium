<?php

declare(strict_types=1);

namespace TicketTest\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\TicketResponse;
use Ticket\Repository\TicketResponseRepository;

class TicketResponseRepositoryTest extends TestCase
{
    private TicketResponseRepository $repository;
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private Query $query;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder  = $this->createMock(QueryBuilder::class);
        $this->query         = $this->createMock(Query::class);

        // Create repository with mocked dependencies
        $this->repository = $this->getMockBuilder(TicketResponseRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);
    }

    public function testFindTicketResponsesByTicketId(): void
    {
        $ticketId  = 123;
        $response1 = $this->createMock(TicketResponse::class);
        $response2 = $this->createMock(TicketResponse::class);
        $responses = [$response1, $response2];

        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('r')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(TicketResponse::class, 'r')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with('r.ticket = :id')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('r.id', 'ASC')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('id', $ticketId)
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($responses);

        $result = $this->repository->findTicketResponsesByTicketId($ticketId);

        $this->assertSame($responses, $result);
    }

    public function testFindTicketResponsesByTicketIdWithNoResponses(): void
    {
        $ticketId = 456;

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);

        $this->query->method('getResult')->willReturn([]);

        $result = $this->repository->findTicketResponsesByTicketId($ticketId);

        $this->assertEquals([], $result);
    }

    public function testFindTicketResponsesByTicketIdUsesCorrectQueryBuilderAlias(): void
    {
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('qb')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);

        $this->repository->findTicketResponsesByTicketId(123);
    }

    public function testFindTicketResponsesByTicketIdOrdersResponsesById(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);

        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('r.id', 'ASC')
            ->willReturn($this->queryBuilder);

        $this->repository->findTicketResponsesByTicketId(123);
    }

    public function testFindTicketResponsesByTicketIdUsesCorrectEntityClass(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('setParameter')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(TicketResponse::class, 'r')
            ->willReturn($this->queryBuilder);

        $this->repository->findTicketResponsesByTicketId(123);
    }

    public function testFindTicketResponsesByTicketIdUsesCorrectParameterBinding(): void
    {
        $ticketId = 789;

        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('where')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('id', $ticketId)
            ->willReturn($this->queryBuilder);

        $this->repository->findTicketResponsesByTicketId($ticketId);
    }
}
