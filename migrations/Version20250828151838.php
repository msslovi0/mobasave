<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828151838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_vehicle (id INT AUTO_INCREMENT NOT NULL, maker_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, year INT DEFAULT NULL, registration VARCHAR(255) DEFAULT NULL, INDEX IDX_E6119B1C68DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_vehicle ADD CONSTRAINT FK_E6119B1C68DA5EC3 FOREIGN KEY (maker_id) REFERENCES mbs_maker (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_vehicle DROP FOREIGN KEY FK_E6119B1C68DA5EC3');
        $this->addSql('DROP TABLE mbs_vehicle');
    }
}
