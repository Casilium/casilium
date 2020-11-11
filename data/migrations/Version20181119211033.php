<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use phpDocumentor\Reflection\Types\Void_;

/**
 * Creates the schema required for users
 */
final class Version20181119211033 extends AbstractMigration
{
    const USER_TABLE = 'user';

    /**
     * Returns the description of this migration.
     * @return string
     */
    public function getDescription(): string
    {
        $description = 'This is the initial migration which creates the users table';
        return $description;
    }

    /**
     * Updates the schema to its newer state.
     * @param Schema $schema
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

    public function postUp(Schema $schema): void
    {
        // create initial admin user, password = Password1
        $this->connection->insert('user', [
            'email' => 'admin@example.com',
            'password' => '$2y$12$lZHLgnuwJSyTRgErOESY6OX2SXJuyYYqpAisdRfQEvrPN4QLg.jjW',
            'full_name' => 'Administrator',
            'date_created' => date('Y-m-d H:i:s'),
            'status' => 1,
        ]);
    }

    /**
     * Reverts the schema changes.
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable(self::USER_TABLE);
        $table->dropIndex('email_index');
        $schema->dropTable(self::USER_TABLE);
    }
}
