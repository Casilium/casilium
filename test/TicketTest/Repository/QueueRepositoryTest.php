<?php

declare(strict_types=1);

namespace TicketTest\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\Queue;
use Ticket\Repository\QueueRepository;

class QueueRepositoryTest extends TestCase
{
    private QueueRepository $repository;
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private Query $query;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);
        
        // Create repository with mocked dependencies
        $this->repository = $this->getMockBuilder(QueueRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
            
        $this->repository->method('createQueryBuilder')->willReturn($this->queryBuilder);
    }

    public function testFindAllReturnsOrderedQueues(): void
    {
        $queue1 = $this->createMock(Queue::class);
        $queue2 = $this->createMock(Queue::class);
        $queues = [$queue1, $queue2];
        
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->with('q')
            ->willReturn($this->queryBuilder);
            
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(Queue::class, 'q')
            ->willReturn($this->queryBuilder);
            
        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('q.name')
            ->willReturn($this->queryBuilder);
            
        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
            
        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($queues);
        
        $result = $this->repository->findAll();
        
        $this->assertSame($queues, $result);
    }

    public function testFindAllReturnsEmptyArrayWhenNoQueues(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        
        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);
        
        $result = $this->repository->findAll();
        
        $this->assertEquals([], $result);
    }

    public function testFindAllUsesCorrectQueryBuilderAlias(): void
    {
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('qb')
            ->willReturn($this->queryBuilder);
        
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);
        
        $this->repository->findAll();
    }

    public function testFindAllOrdersByQueueName(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);
        
        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('q.name')
            ->willReturn($this->queryBuilder);
        
        $this->repository->findAll();
    }

    public function testFindAllUsesCorrectEntityClass(): void
    {
        $this->queryBuilder->method('select')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('orderBy')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('getQuery')->willReturn($this->query);
        $this->query->method('getResult')->willReturn([]);
        
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(Queue::class, 'q')
            ->willReturn($this->queryBuilder);
        
        $this->repository->findAll();
    }
}