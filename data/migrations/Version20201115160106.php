<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115160106 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Provides schema for organisation contacts';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('organisation_contact');

        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $table->addColumn('organisation_id', 'integer', ['notnull' => true, 'unsigned' => true]);
        $table->addColumn('site_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('first_name', 'string', ['notnull' => true]);
        $table->addColumn('middle_name', 'string', ['notnull' => false]);
        $table->addColumn('last_name', 'string', ['notnull' => true]);
        $table->addColumn('work_telephone', 'string', ['notnull' => false]);
        $table->addColumn('work_extension', 'string', ['notnull' => false]);
        $table->addColumn('mobile_telephone', 'string', ['notnull' => false]);
        $table->addColumn('home_telephone', 'string', ['notnull' => false]);
        $table->addColumn('work_email', 'string', ['notnull' => true]);
        $table->addColumn('other_email', 'string', ['notnull' => false]);
        $table->addColumn('gender', 'string', ['notnull' => false, 'length' => 1]);

        $table->addForeignKeyConstraint('organisation',['organisation_id'], ['id'],
            ['onDelete'=>'RESTRICT', 'onUpdate'=>'CASCADE'], 'employee_organisation_id_fk');

        $table->addForeignKeyConstraint('organisation_site', ['site_id'], ['id'],
            ['onDelete'=>'RESTRICT', 'onUpdate'=>'CASCADE'], 'employee_site_id_fk');

        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('organisation_contact');

    }
}
