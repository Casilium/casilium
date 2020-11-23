<?php

declare(strict_types=1);

namespace Ticket\Service;

use App\Encryption\Sodium;
use Doctrine\ORM\EntityManagerInterface;
use Ticket\Entity\Queue;

class QueueManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $config;

    public function __construct(EntityManagerInterface $entityManager, array $config)
    {
        $this->entityManager = $entityManager;
        $this->config        = $config;

        if (! array_key_exists('key', $config)) {
            throw new \Exception('Encryption key not found');
        }
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

    /**
     * Save/update Queue;
     *
     * @param Queue $queue Queue to save
     * @return Queue saved queue object
     */
    public function save(Queue $queue): Queue
    {
        $queue->setPassword(Sodium::encrypt($queue->getPassword(), $this->config['key']));

        $this->entityManager->persist($queue);
        $this->entityManager->flush();

        return $queue;
    }
}
