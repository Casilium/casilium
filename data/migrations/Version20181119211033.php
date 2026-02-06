<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\Migrations\AbstractMigration;

use function date;

/**
 * Creates the schema required for users
 */
final class Version20181119211033 extends AbstractMigration
{
    private const USER_TABLE = 'user';

    /**
     * Returns the description of this migration.
     */
    public function getDescription(): string
    {
        return 'This is the initial migration which creates the users table';
    }

    /**
     * Updates the schema to its newer state.
     */
    public function up(Schema $schema): void
    {
        // Create users table
        $table = $schema->createTable(self::USER_TABLE);
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('email', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('password', 'string', ['notnull' => true, 'length' => 64]);
        $table->addColumn('full_name', 'string', ['notnull' => true, 'length' => 64]);
        $table->addColumn('status', 'smallint', ['notnull' => true, 'unsigned' => true, 'default' => 0]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->addColumn('pwd_reset_token', 'string', ['length' => 128, 'notnull' => false]);
        $table->addColumn('pwd_reset_token_creation_date', 'datetime', ['notnull' => false]);
        $table->addColumn('secret_key', 'string', ['notnull' => false]);
        $table->addColumn('mfa_enabled', 'smallint', ['notnull' => true, 'unsigned' => true, 'default' => 0]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'email_index', []);
        $table->addOption('engine', 'InnoDB');
    }

    /**
     * Reverts the schema changes.
     *
     * @throws SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable(self::USER_TABLE);
        $table->dropIndex('email_index');
        $schema->dropTable(self::USER_TABLE);
    }
}
