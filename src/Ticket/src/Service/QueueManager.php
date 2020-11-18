<?php

declare(strict_types=1);

namespace Ticket\Service;

use Doctrine\ORM\EntityManagerInterface;
use Ticket\Entity\Queue;

class QueueManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findQueueById(int $id): Queue
    {
        return $this->entityManager->getRepository(Queue::class)->find($id);
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Queue::class)->findAll();
    }
}
