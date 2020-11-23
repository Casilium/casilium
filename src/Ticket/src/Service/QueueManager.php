<?php

declare(strict_types=1);

namespace Ticket\Service;

use App\Encryption\Sodium;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ticket\Entity\Queue;
use function array_key_exists;

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
            throw new Exception('Encryption key not found');
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
     * @param array $data Queue to save
     * @return Queue saved queue object
     */
    public function save(array $data): Queue
    {
        $queueId = (int) $data['id'] ?? 0;

        if ($queueId > 0) {
            $q = new Queue();
        } else {
            $q = $this->findQueueById($queueId);
        }

        $q->setName($data['name']);
        $q->setEmail($data['email'] ?? null);
        $q->setPassword($data['password'] ?? null);
        $q->setHost($data['host'] ?? null);
        $q->setUser($data['user'] ?? null);
        if (! empty($data['password'])) {
            $q->setPassword(Sodium::encrypt($data['password'], $this->config['key']));
        }
        $q->setUseSsl((bool) $data['use_ssl'] ?? null);
        $q->setFetchFromMail((bool) $data['fetch_from_mail'] ?? null);

        if (! empty($q->getPassword())) {
            $q->setPassword(Sodium::encrypt($q->getPassword(), $this->config['key']));
        }

        if (null === $queueId) {
            $this->entityManager->persist($q);
        }
        $this->entityManager->flush();

        return $q;
    }
}
