<?php

declare(strict_types=1);

namespace Ticket\Service;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ticket\Entity\CannedResponse;

use function sprintf;

class CannedResponseManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return CannedResponse[] */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(CannedResponse::class)
            ->findBy([], ['title' => 'ASC']);
    }

    public function findById(int $id): ?CannedResponse
    {
        return $this->entityManager->getRepository(CannedResponse::class)->find($id);
    }

    /** @param array{id?: int|string|null, title: string, response: string} $data */
    public function save(array $data): CannedResponse
    {
        $id = (int) ($data['id'] ?? 0);

        if ($id === 0) {
            $cannedResponse = new CannedResponse();
            $this->entityManager->persist($cannedResponse);
        } else {
            $cannedResponse = $this->findById($id);
            if ($cannedResponse === null) {
                throw new InvalidArgumentException(sprintf('Canned response %d does not exist', $id));
            }
        }

        $cannedResponse->setTitle($data['title']);
        $cannedResponse->setResponse($data['response']);

        $this->entityManager->flush();

        return $cannedResponse;
    }

    public function delete(CannedResponse $cannedResponse): void
    {
        $this->entityManager->remove($cannedResponse);
        $this->entityManager->flush();
    }
}
