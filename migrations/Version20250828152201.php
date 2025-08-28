<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828152201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tram (id INT AUTO_INCREMENT NOT NULL, maker_id INT DEFAULT NULL, axle_id INT DEFAULT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) NOT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, INDEX IDX_DD8913EA68DA5EC3 (maker_id), INDEX IDX_DD8913EA9EE696DC (axle_id), INDEX IDX_DD8913EAAB4FC384 (power_id), INDEX IDX_DD8913EA248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tram ADD CONSTRAINT FK_DD8913EA68DA5EC3 FOREIGN KEY (maker_id) REFERENCES mbs_maker (id)');
        $this->addSql('ALTER TABLE tram ADD CONSTRAINT FK_DD8913EA9EE696DC FOREIGN KEY (axle_id) REFERENCES mbs_axle (id)');
        $this->addSql('ALTER TABLE tram ADD CONSTRAINT FK_DD8913EAAB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE tram ADD CONSTRAINT FK_DD8913EA248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
        $this->addSql('ALTER TABLE mbs_vehicle CHANGE name class VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tram DROP FOREIGN KEY FK_DD8913EA68DA5EC3');
        $this->addSql('ALTER TABLE tram DROP FOREIGN KEY FK_DD8913EA9EE696DC');
        $this->addSql('ALTER TABLE tram DROP FOREIGN KEY FK_DD8913EAAB4FC384');
        $this->addSql('ALTER TABLE tram DROP FOREIGN KEY FK_DD8913EA248A9586');
        $this->addSql('DROP TABLE tram');
        $this->addSql('ALTER TABLE mbs_vehicle CHANGE class name VARCHAR(255) NOT NULL');
    }
}
