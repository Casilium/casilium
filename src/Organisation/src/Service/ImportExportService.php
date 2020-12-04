<?php

declare(strict_types=1);

namespace Organisation\Service;

use Doctrine\Dbal\Connection;
use Doctrine\ORM\EntityManagerInterface;
use function array_keys;

class ImportExportService
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var Connection */
    protected $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection    = $entityManager->getConnection();
    }

    public function fetchOrganisations(): array
    {
        $sql   = 'SELECT * FROM organisation';
        $query = $this->connection->prepare($sql);

        $query->execute();
        $result = $query->fetchAllAssociative();

        $header = array_keys($result[0]);

        return [
            'columns' => $query->columnCount(),
            'rows'    => $query->rowCount(),
            'headers' => $header,
            'content' => $result,
        ];
    }
}
