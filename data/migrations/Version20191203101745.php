<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203101745 extends AbstractMigration
{
    /**
     * Name of our organisation table
     * @var string
     */
    protected $table_name = 'organisation';

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getDescription() : string
    {
        return 'Provides organisation schema';
    }

    /**
     * {@inheritDoc}
     *
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // organisation
        $table = $schema->createTable($this->table_name);
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $table->addColumn('uuid', 'uuid');
        $table->addColumn('created', 'datetime');
        $table->addColumn('is_active', 'integer');
        $table->addColumn('type_id', 'integer');
        $table->addColumn('modified', 'datetime');
        $table->addColumn('name', 'string', ['length' => 128]);
        $table->addUniqueIndex(['name']);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');
    }

    /**
     * {@inheritDoc}
     *
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $schema->dropTable($this->table_name);
    }
}
