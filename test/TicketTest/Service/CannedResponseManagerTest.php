<?php

declare(strict_types=1);

namespace TicketTest\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\CannedResponse;
use Ticket\Service\CannedResponseManager;

class CannedResponseManagerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private CannedResponseManager $manager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository    = $this->createMock(EntityRepository::class);
        $this->manager       = new CannedResponseManager($this->entityManager);

        $this->entityManager->method('getRepository')
            ->with(CannedResponse::class)
            ->willReturn($this->repository);
    }

    public function testFindAllOrdersResponsesByTitle(): void
    {
        $responses = [new CannedResponse(), new CannedResponse()];

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with([], ['title' => 'ASC'])
            ->willReturn($responses);

        $this->assertSame($responses, $this->manager->findAll());
    }

    public function testCreatesCannedResponse(): void
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CannedResponse::class));
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->manager->save([
            'id'       => null,
            'title'    => 'Password reset',
            'response' => 'Please reset your password.',
        ]);

        $this->assertSame('Password reset', $response->getTitle());
        $this->assertSame('Please reset your password.', $response->getResponse());
    }

    public function testUpdatesCannedResponse(): void
    {
        $existing = new CannedResponse();
        $existing->setId(8);
        $existing->setTitle('Old title');
        $existing->setResponse('Old response');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(8)
            ->willReturn($existing);
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->manager->save([
            'id'       => 8,
            'title'    => 'New title',
            'response' => 'New response',
        ]);

        $this->assertSame($existing, $response);
        $this->assertSame('New title', $response->getTitle());
        $this->assertSame('New response', $response->getResponse());
    }

    public function testDeletesCannedResponse(): void
    {
        $response = new CannedResponse();

        $this->entityManager->expects($this->once())->method('remove')->with($response);
        $this->entityManager->expects($this->once())->method('flush');

        $this->manager->delete($response);
    }
}
