<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Generate tables for Business Hours (SLA)
 */
final class Version20201125142408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Generate table for business hours (SLA)';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('business_hours');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true]);
        $table->addColumn('timezone', 'smallint', ['notnull' => true, 'unsigned' => true]);
        $table->addColumn('mon_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('mon_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('tue_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('tue_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('wed_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('wed_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('thu_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('thu_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('fri_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('fri_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('sat_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('sat_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('sun_start', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('sun_end', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('mon_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('tue_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('wed_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('thu_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('fri_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('sat_active', 'smallint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('sun_active', 'smallint', ['notnull' => false, 'unsigned' => true]);

        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('business_hours');
    }
}
