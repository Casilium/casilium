<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create database schema for Organisations
 */
final class Version20191203101745 extends AbstractMigration
{
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
        // create domains table
        $table = $schema->createTable('organisation_domain');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $table->addColumn('organisation_id', 'integer', ['unsigned' => true]);
        $table->addColumn('name', 'string', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'domain_index', []);
        $table->addOption('engine', 'InnoDB');

        $table->addForeignKeyConstraint('organisation',['organisation_id'], ['id'],
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'org_domain_fk');

        // organisation
        $table = $schema->createTable('organisation');
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
        $schema->dropTable('organisation');
        $schema->dropTable('organisation_domain');
    }
}
