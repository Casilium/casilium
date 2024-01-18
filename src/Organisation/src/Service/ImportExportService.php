<?php

declare(strict_types=1);

namespace Organisation\Service;

use Doctrine\Dbal\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

use function array_keys;

class ImportExportService
{
    protected EntityManagerInterface $entityManager;

    protected Connection $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection    = $entityManager->getConnection();
    }

    /**
     * @throws Exception
     */
    public function fetchOrganisations(): array
    {
        $sql  = 'SELECT * FROM organisation';
        $stmt = $this->connection->prepare($sql);

        $query  = $stmt->executeQuery();
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
