<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use function date;

final class Version20260206190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ticket.manage permission and assign to Administrator role';
    }

    public function up(Schema $schema): void
    {
        // Add permission if missing
        $this->addSql(
            "INSERT INTO permission (name, description, date_created)
             SELECT 'ticket.manage', 'Manage tickets', :date_created
             WHERE NOT EXISTS (SELECT 1 FROM permission WHERE name = 'ticket.manage')",
            ['date_created' => date('Y-m-d H:i:s')]
        );

        // Assign to Administrator role if both exist and mapping missing
        $this->addSql(
            "INSERT INTO role_permission (role_id, permission_id)
             SELECT r.id, p.id
             FROM role r
             JOIN permission p ON p.name = 'ticket.manage'
             WHERE r.name = 'Administrator'
               AND NOT EXISTS (
                   SELECT 1 FROM role_permission rp
                   WHERE rp.role_id = r.id AND rp.permission_id = p.id
               )"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE rp
             FROM role_permission rp
             JOIN permission p ON p.id = rp.permission_id
             JOIN role r ON r.id = rp.role_id
             WHERE p.name = 'ticket.manage' AND r.name = 'Administrator'"
        );
        $this->addSql("DELETE FROM permission WHERE name = 'ticket.manage'");
    }
}
