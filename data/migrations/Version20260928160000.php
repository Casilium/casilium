<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260928160000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create canned responses for ticket replies';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('canned_response');
        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('title', Types::STRING, ['length' => 128]);
        $table->addColumn('response', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['title'], 'canned_response_title_unique');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $schema->dropTable('canned_response');
    }
}
