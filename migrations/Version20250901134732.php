<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901134732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBD8177B3F FOREIGN KEY (box_id) REFERENCES mbs_box (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBB8C818A4 FOREIGN KEY (modelcondition_id) REFERENCES mbs_condition (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBD8177B3F');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBB8C818A4');
    }
}
