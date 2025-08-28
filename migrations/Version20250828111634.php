<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828111634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_scale_track (id INT AUTO_INCREMENT NOT NULL, scale_id INT NOT NULL, name VARCHAR(255) NOT NULL, width DOUBLE PRECISION DEFAULT NULL, INDEX IDX_E85AD7F2F73142C2 (scale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_scale_track ADD CONSTRAINT FK_E85AD7F2F73142C2 FOREIGN KEY (scale_id) REFERENCES mbs_scale (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_scale_track DROP FOREIGN KEY FK_E85AD7F2F73142C2');
        $this->addSql('DROP TABLE mbs_scale_track');
    }
}
