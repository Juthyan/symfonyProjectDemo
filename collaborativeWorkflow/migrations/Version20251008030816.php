<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour corriger l'ID de la table task_state (séquence)
 * et insérer les états de base ("To do", "On going", "Finished").
 */
final class Version20251008030816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrects task_state ID sequence and inserts initial data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task_state ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE task_state ALTER id SET DEFAULT nextval(\'task_state_id_seq\')');

        $this->addSql("INSERT INTO task_state (name, \"order\") VALUES ('To do', 1)");
        $this->addSql("INSERT INTO task_state (name, \"order\") VALUES ('On going', 2)");
        $this->addSql("INSERT INTO task_state (name, \"order\") VALUES ('Finished', 3)");
        $this->addSql("SELECT setval('task_state_id_seq', (SELECT COALESCE(MAX(id), 1) FROM task_state), true)");
    }
     
    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM task_state WHERE name IN ('To do', 'On going', 'Finished')");
        $this->addSql('ALTER TABLE task_state ALTER id DROP DEFAULT');
        $this->addSql("SELECT setval('task_state_id_seq', (SELECT COALESCE(MAX(id), 1) FROM task_state), false)");
    }
}
