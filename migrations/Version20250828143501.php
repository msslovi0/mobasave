<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828143501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE waggon (id INT AUTO_INCREMENT NOT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) DEFAULT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_A0B1EB0FAB4FC384 (power_id), INDEX IDX_A0B1EB0F248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE waggon ADD CONSTRAINT FK_A0B1EB0FAB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE waggon ADD CONSTRAINT FK_A0B1EB0F248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE waggon DROP FOREIGN KEY FK_A0B1EB0FAB4FC384');
        $this->addSql('ALTER TABLE waggon DROP FOREIGN KEY FK_A0B1EB0F248A9586');
        $this->addSql('DROP TABLE waggon');
    }
}
