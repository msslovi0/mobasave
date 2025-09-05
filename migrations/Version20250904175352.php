<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904175352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_modelset (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_E907BC2EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_modelset ADD CONSTRAINT FK_E907BC2EA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('ALTER TABLE mbs_model ADD modelset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBA480C173 FOREIGN KEY (modelset_id) REFERENCES mbs_modelset (id)');
        $this->addSql('CREATE INDEX IDX_E80420CBA480C173 ON mbs_model (modelset_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBA480C173');
        $this->addSql('ALTER TABLE mbs_modelset DROP FOREIGN KEY FK_E907BC2EA76ED395');
        $this->addSql('DROP TABLE mbs_modelset');
        $this->addSql('DROP INDEX IDX_E80420CBA480C173 ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model DROP modelset_id');
    }
}
