<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Record how long a ticket has spent on hold.
 *
 * updateWaitingTickets already works out how much time has passed while a
 * ticket was waiting, uses it to push the due date out, and throws it away.
 * Keeping a running total lets resolution time be reported net of waiting,
 * so a ticket parked for weeks on a hardware order stops being counted as
 * weeks of work.
 *
 * This only accumulates from here on. waiting_reset_date is overwritten on
 * every run, so there is no history to populate it from.
 */
final class Version20260929020000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Record how long a ticket has spent on hold';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $schema->getTable('ticket')->addColumn('held_minutes', Types::INTEGER, [
            'unsigned' => true,
            'notnull'  => true,
            'default'  => 0,
        ]);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $schema->getTable('ticket')->dropColumn('held_minutes');
    }
}
