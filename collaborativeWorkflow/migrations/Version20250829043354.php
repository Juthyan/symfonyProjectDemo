<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829043354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE role ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ADD roles JSON NOT NULL DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE "user" ADD password VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" DROP password');
        $this->addSql('CREATE SEQUENCE role_id_seq');
        $this->addSql('SELECT setval(\'role_id_seq\', (SELECT MAX(id) FROM role))');
        $this->addSql('ALTER TABLE role ALTER id SET DEFAULT nextval(\'role_id_seq\')');
    }
}
