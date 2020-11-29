<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use function date;

/**
 * Creates the rbac tables for user permissions
 */
final class Version20181119222947 extends AbstractMigration
{
    /**
     * Returns the description of this migration
     */
    public function getDescription(): string
    {
        return 'A migration which creates the role and permissions tables';
    }

    /**
     * Upgrades the schema to its newer state
     */
    public function up(Schema $schema): void
    {
        // Create 'role' table
        $table = $schema->createTable('role');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('description', 'string', ['notnull' => true, 'length' => 1024]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addOption('engine', 'InnoDB');

        // Create 'role_hierarchy' table (contains parent-child relationships between roles)
        $table = $schema->createTable('role_hierarchy');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('parent_role_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('child_role_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'role',
            ['parent_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_role_parent_role_id_fk'
        );
        $table->addForeignKeyConstraint(
            'role',
            ['child_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_role_child_role_id_fk'
        );
        $table->addOption('engine', 'InnoDB');

        // Create 'permission' table
        $table = $schema->createTable('permission');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('description', 'string', ['notnull' => true, 'length' => 1024]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addOption('engine', 'InnoDB');

        // Create 'role_permission' table
        $table = $schema->createTable('role_permission');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('role_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('permission_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'role',
            ['role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_permission_role_id_fk'
        );
        $table->addForeignKeyConstraint(
            'permission',
            ['permission_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_permission_permission_id_fk'
        );
        $table->addOption('engine', 'InnoDB');

        // Create 'user_role' table
        $table = $schema->createTable('user_role');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('role_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'user',
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'user_role_user_id_fk'
        );
        $table->addForeignKeyConstraint(
            'role',
            ['role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'user_role_role_id_fk'
        );
        $table->addOption('engine', 'InnoDB');
    }

    public function postUp(Schema $schema): void
    {
        // create default permissions
        $this->connection->insert('permission', [
            'id'           => 1,
            'name'         => 'user.manage',
            'description'  => 'Manage users',
            'date_created' => date('Y-m-d H:i:s'),
        ]);
        $this->connection->insert('permission', [
            'id'           => 2,
            'name'         => 'permission.manage',
            'description'  => 'Manage permissions',
            'date_created' => date('Y-m-d H:i:s'),
        ]);
        $this->connection->insert('permission', [
            'id'           => 3,
            'name'         => 'role.manage',
            'description'  => 'Manage roles',
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        // create default roles
        $this->connection->insert('role', [
            'id'           => 1,
            'name'         => 'Administrator',
            'description'  => 'A person who manages users, roles, etc.',
            'date_created' => date('Y-m-d H:i:s'),
        ]);
        // create default roles
        $this->connection->insert('role', [
            'id'           => 2,
            'name'         => 'Guest',
            'description'  => 'Restricted user, no permissions',
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        // assign admin role to user 1
        $this->connection->insert('user_role', [
            'user_id' => 1,
            'role_id' => 1,
        ]);

        // assign permissions to admin role
        $this->connection->insert('role_permission', [
            'role_id'       => 1,
            'permission_id' => 1,
        ]);
        // assign permissions to admin role
        $this->connection->insert('role_permission', [
            'role_id'       => 1,
            'permission_id' => 2,
        ]);
        // assign permissions to admin role
        $this->connection->insert('role_permission', [
            'role_id'       => 1,
            'permission_id' => 3,
        ]);
    }

    /**
     * Reverts the schema changes
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('user_role');
        $schema->dropTable('role_hierarchy');
        $schema->dropTable('role_permission');
        $schema->dropTable('permission');
        $schema->dropTable('role');
    }
}
