<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901134524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `condition` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BDD68843A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `condition` ADD CONSTRAINT FK_BDD68843A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('ALTER TABLE mbs_model ADD modelcondition_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBB8C818A4 FOREIGN KEY (modelcondition_id) REFERENCES `condition` (id)');
        $this->addSql('CREATE INDEX IDX_E80420CBB8C818A4 ON mbs_model (modelcondition_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBB8C818A4');
        $this->addSql('ALTER TABLE `condition` DROP FOREIGN KEY FK_BDD68843A76ED395');
        $this->addSql('DROP TABLE `condition`');
        $this->addSql('DROP INDEX IDX_E80420CBB8C818A4 ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model DROP modelcondition_id');
    }
}
