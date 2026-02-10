<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209162124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_active to organisation_contact with default true';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE organisation_contact
            ADD is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER gender"
        );

        $this->addSql(
            "CREATE INDEX idx_org_contact_active
            ON organisation_contact (organisation_id, is_active)"
        );
    }

    public function down(Schema $schema): void
    {
          $this->addSql("DROP INDEX idx_org_contact_active ON organisation_contact");
          $this->addSql(
              "ALTER TABLE organisation_contact
               DROP COLUMN is_active"
          );
    }
}
