<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828152539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_car (id INT AUTO_INCREMENT NOT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) NOT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_9751E66EAB4FC384 (power_id), INDEX IDX_9751E66E248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_car ADD CONSTRAINT FK_9751E66EAB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE mbs_car ADD CONSTRAINT FK_9751E66E248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_car DROP FOREIGN KEY FK_9751E66EAB4FC384');
        $this->addSql('ALTER TABLE mbs_car DROP FOREIGN KEY FK_9751E66E248A9586');
        $this->addSql('DROP TABLE mbs_car');
    }
}
