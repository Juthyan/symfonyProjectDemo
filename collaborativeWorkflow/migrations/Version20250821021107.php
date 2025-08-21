<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821021107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO role (name, can_edit, can_delete, can_invite) VALUES ('admin', true, true, true)");
        $this->addSql("INSERT INTO role (name, can_edit, can_delete, can_invite) VALUES ('editor', true, false, false)");
        $this->addSql("INSERT INTO role (name, can_edit, can_delete, can_invite) VALUES ('viewer', false, false, false)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM role WHERE name IN ('admin', 'editor', 'viewer')");
    }
}
