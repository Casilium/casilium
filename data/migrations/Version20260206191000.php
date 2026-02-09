<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create default business hours and standard SLA targets';
    }

    public function up(Schema $schema): void
    {
        // Only seed defaults when both tables are empty (fresh install).
        $hasBusinessHours = (bool) $this->connection->fetchOne('SELECT 1 FROM business_hours LIMIT 1');
        $hasSla           = (bool) $this->connection->fetchOne('SELECT 1 FROM sla LIMIT 1');

        if ($hasBusinessHours || $hasSla) {
            return;
        }

        // Default business hours: Mon-Fri 09:00-17:00, UTC
        $this->addSql(
            "INSERT INTO business_hours
                (name, timezone, mon_start, mon_end, tue_start, tue_end, wed_start, wed_end,
                 thu_start, thu_end, fri_start, fri_end, sat_start, sat_end, sun_start, sun_end,
                 mon_active, tue_active, wed_active, thu_active, fri_active, sat_active, sun_active)
             SELECT
                'Default (Mon-Fri 9-5)', 'UTC',
                '09:00', '17:00', '09:00', '17:00', '09:00', '17:00',
                '09:00', '17:00', '09:00', '17:00', NULL, NULL, NULL, NULL,
                1, 1, 1, 1, 1, 0, 0
             WHERE NOT EXISTS (
                SELECT 1 FROM business_hours WHERE name = 'Default (Mon-Fri 9-5)'
             )"
        );

        // Standard SLA linked to default business hours
        $this->addSql(
            "INSERT INTO sla (name, business_hours_id)
             SELECT 'Standard', bh.id
             FROM business_hours bh
             WHERE bh.name = 'Default (Mon-Fri 9-5)'
               AND NOT EXISTS (SELECT 1 FROM sla WHERE name = 'Standard')"
        );

        // SLA targets for Standard SLA (priority ids: 2 Critical, 3 Urgent, 4 High, 5 Medium, 6 Low)
        $this->addSql(
            "INSERT INTO sla_target (sla_id, priority_id, response_time, resolve_time)
             SELECT s.id, 2, '01:00', '02:00'
             FROM sla s
             WHERE s.name = 'Standard'
               AND NOT EXISTS (
                 SELECT 1 FROM sla_target t WHERE t.sla_id = s.id AND t.priority_id = 2
               )"
        );
        $this->addSql(
            "INSERT INTO sla_target (sla_id, priority_id, response_time, resolve_time)
             SELECT s.id, 3, '01:00', '04:00'
             FROM sla s
             WHERE s.name = 'Standard'
               AND NOT EXISTS (
                 SELECT 1 FROM sla_target t WHERE t.sla_id = s.id AND t.priority_id = 3
               )"
        );
        $this->addSql(
            "INSERT INTO sla_target (sla_id, priority_id, response_time, resolve_time)
             SELECT s.id, 4, '02:00', '08:00'
             FROM sla s
             WHERE s.name = 'Standard'
               AND NOT EXISTS (
                 SELECT 1 FROM sla_target t WHERE t.sla_id = s.id AND t.priority_id = 4
               )"
        );
        $this->addSql(
            "INSERT INTO sla_target (sla_id, priority_id, response_time, resolve_time)
             SELECT s.id, 5, '08:00', '16:00'
             FROM sla s
             WHERE s.name = 'Standard'
               AND NOT EXISTS (
                 SELECT 1 FROM sla_target t WHERE t.sla_id = s.id AND t.priority_id = 5
               )"
        );
        $this->addSql(
            "INSERT INTO sla_target (sla_id, priority_id, response_time, resolve_time)
             SELECT s.id, 6, '16:00', '40:00'
             FROM sla s
             WHERE s.name = 'Standard'
               AND NOT EXISTS (
                 SELECT 1 FROM sla_target t WHERE t.sla_id = s.id AND t.priority_id = 6
               )"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE t
             FROM sla_target t
             JOIN sla s ON s.id = t.sla_id
             WHERE s.name = 'Standard'"
        );
        $this->addSql("DELETE FROM sla WHERE name = 'Standard'");
        $this->addSql("DELETE FROM business_hours WHERE name = 'Default (Mon-Fri 9-5)'");
    }
}
