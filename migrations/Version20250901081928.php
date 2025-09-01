<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901081928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_model ADD status_id INT DEFAULT NULL, DROP box');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB6BF700BD FOREIGN KEY (status_id) REFERENCES mbs_status (id)');
        $this->addSql('CREATE INDEX IDX_E80420CB6BF700BD ON mbs_model (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB6BF700BD');
        $this->addSql('DROP TABLE mbs_status');
        $this->addSql('DROP INDEX IDX_E80420CB6BF700BD ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model ADD box LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP status_id');
    }
}
