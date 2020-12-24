<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201117165118 extends AbstractMigration
{
    /**
     * Create database tables
     */
    public function up(Schema $schema): void
    {
        $this->createTicketStatusTable($schema);
        $this->createPriorityTable($schema);
        $this->createTicketTypeTable($schema);
        $this->createTicketSourceTable($schema);
        $this->createQueueTable($schema);
        $this->createQueueMemberTable($schema);
        $this->createTicketTable($schema);
        $this->createTicketResponseTable($schema);
    }

    /**
     * Ticket Status table
     */
    public function createTicketStatusTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket_status');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('description', 'string', ['notnull' => true]);
        $table->addUniqueIndex(['description']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create table for ticket priority
     */
    public function createPriorityTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket_priority');
        $table->addColumn('id', 'smallint', ['unsigned' => true]);
        $table->addColumn('name', 'string', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);
    }

    public function createTicketTypeTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket_type');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('description', 'string', ['notnull' => true]);
        $table->addUniqueIndex(['description']);
        $table->setPrimaryKey(['id']);
    }

    public function createTicketSourceTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket_source');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('description', 'string', ['notnull' => true]);
        $table->addUniqueIndex(['description']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create table for ticket Queues
     */
    public function createQueueTable(Schema $schema): void
    {
        $table = $schema->createTable('queue');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true]);
        $table->addColumn('email', 'string', ['notnull' => false]);
        $table->addColumn('host', 'string', ['notnull' => false]);
        $table->addColumn('user', 'string', ['notnull' => false]);
        $table->addColumn('password', 'string', ['notnull' => false]);
        $table->addColumn('use_ssl', 'boolean', ['notnull' => false]);
        $table->addColumn('fetch_from_mail', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);
    }

    public function createQueueMemberTable(Schema $schema): void
    {
        $table = $schema->createTable('queue_member');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('queue_id', 'integer', ['unsigned' => true]);
        $table->addColumn('user_id', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);

        $table->addForeignKeyConstraint(
            'queue',
            ['queue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'queue_member_queue_id_fk'
        );

        $table->addForeignKeyConstraint(
            'user',
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'queue_member_user_id_fk'
        );
    }

    /**
     * Ticket Table
     */
    public function createTicketTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('uuid', 'string', ['length' => 36, 'fixed' => true, 'notnull' => true]);
        $table->addColumn('source_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('organisation_id', 'integer', ['unsigned' => true]);
        $table->addColumn('site_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['unsigned' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('agent_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('impact', 'smallint', ['unsigned' => true]);
        $table->addColumn('urgency', 'smallint', ['unsigned' => true]);
        $table->addColumn('priority_id', 'smallint', ['unsigned' => true]);
        $table->addColumn('short_description', 'string', ['notnull' => true]);
        $table->addColumn('long_description', 'text', ['notnull' => true]);
        $table->addColumn('status', 'integer', ['unsigned' => true]);
        $table->addColumn('queue_id', 'integer', ['unsigned' => true]);
        $table->addColumn('sla_target_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('type_id', 'integer', ['unsigned' => true]);
        $table->addColumn('assigned_agent_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('due_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('close_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('resolve_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('waiting_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('waiting_reset_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('last_response_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('first_response_date', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('last_notified', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn('first_response_due', 'datetime', ['notnull' => false, 'default' => null]);
        $table->addForeignKeyConstraint(
            'user',
            ['assigned_agent_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_assigned_agent_id_user_id_fk'
        );

        $table->addForeignKeyConstraint(
            'ticket_source',
            ['source_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_source_id_source_id_fk'
        );

        $table->addForeignKeyConstraint(
            'queue',
            ['queue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_response_queue_id_ticket_id_fk'
        );

        $table->addForeignKeyConstraint(
            'organisation_site',
            ['site_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_site_id_site_id_fk'
        );

        $table->addForeignKeyConstraint(
            'organisation_contact',
            ['contact_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_contact_id_contact_id_fk'
        );

        $table->addForeignKeyConstraint(
            'ticket_status',
            ['status'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_ticket_status_ticket_status_id_fk'
        );

        $table->addForeignKeyConstraint(
            'ticket_priority',
            ['priority_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_ticket_priority_ticket_priority_id_fk'
        );

        $table->addForeignKeyConstraint(
            'ticket_type',
            ['type_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_type_id_type_id_fk'
        );

        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');
    }

    /**
     * Ticket Response table
     */
    public function createTicketResponseTable(Schema $schema): void
    {
        $table = $schema->createTable('ticket_response');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('ticket_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('response_date', 'datetime', ['notnull' => true]);
        $table->addColumn('agent_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('response', 'text', ['notnull' => true]);
        $table->addColumn('ticket_status', 'integer', ['unsigned' => true]);
        $table->addColumn('is_public', 'smallint', ['unsigned' => true]);
        $table->addIndex(['ticket_id', 'ticket_status', 'response_date']);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');

        $table->addForeignKeyConstraint(
            'ticket',
            ['ticket_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_response_ticket_id_ticket_id_fk'
        );

        $table->addForeignKeyConstraint(
            'ticket_status',
            ['ticket_status'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'ticket_response_status_ticket_status_id_fk'
        );
    }

    /**
     * Insert default values in to Ticket Status table
     *
     * @throws DBALException
     */
    public function setUpTicketStatus(): void
    {
        $this->connection->insert('ticket_status', ['id' => 1, 'description' => 'Open']);
        $this->connection->insert('ticket_status', ['id' => 2, 'description' => 'In-progress']);
        $this->connection->insert('ticket_status', ['id' => 3, 'description' => 'On-hold']);
        $this->connection->insert('ticket_status', ['id' => 4, 'description' => 'Resolved']);
        $this->connection->insert('ticket_status', ['id' => 5, 'description' => 'Closed']);
    }

    public function setUpTicketPriority(): void
    {
        $this->connection->insert('ticket_priority', ['id' => 2, 'name' => 'Critical']);
        $this->connection->insert('ticket_priority', ['id' => 3, 'name' => 'Urgent']);
        $this->connection->insert('ticket_priority', ['id' => 4, 'name' => 'High']);
        $this->connection->insert('ticket_priority', ['id' => 5, 'name' => 'Medium']);
        $this->connection->insert('ticket_priority', ['id' => 6, 'name' => 'Low']);
    }

    public function setUpTicketType(): void
    {
        $this->connection->insert('ticket_type', ['id' => 1, 'description' => 'Request']);
        $this->connection->insert('ticket_type', ['id' => 2, 'description' => 'Incident']);
        $this->connection->insert('ticket_type', ['id' => 3, 'description' => 'Problem']);
    }

    public function setUpTicketSource(): void
    {
        $this->connection->insert('ticket_source', ['id' => 1, 'description' => 'E-Mail']);
        $this->connection->insert('ticket_source', ['id' => 2, 'description' => 'Phone']);
        $this->connection->insert('ticket_source', ['id' => 3, 'description' => 'Web']);
    }

    public function setUpQueues(): void
    {
        $this->connection->insert('queue', ['id' => 1, 'name' => 'Support']);
    }

    /**
     * Call any post table creation routes, such as insert data
     *
     * @throws DBALException
     */
    public function postUp(Schema $schema): void
    {
        $this->setUpQueues();
        $this->setUpTicketStatus();
        $this->setUpTicketPriority();
        $this->setUpTicketType();
        $this->setUpTicketSource();
    }

    /**
     * Revert any changes from this schema update
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('queue_member');
        $schema->dropTable('queue');
        $schema->dropTable('ticket_response');
        $schema->dropTable('ticket_source');
        $schema->dropTable('ticket_type');
        $schema->dropTable('ticket_priority');
        $schema->dropTable('ticket_status');
        $schema->dropTable('ticket');
    }
}
