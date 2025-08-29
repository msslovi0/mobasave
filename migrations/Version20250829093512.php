<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829093512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_waggon DROP FOREIGN KEY FK_53370B15AB4FC384');
        $this->addSql('ALTER TABLE mbs_waggon DROP FOREIGN KEY FK_53370B15248A9586');
        $this->addSql('DROP TABLE mbs_waggon');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_waggon (id INT AUTO_INCREMENT NOT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, registration VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_53370B15AB4FC384 (power_id), INDEX IDX_53370B15248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE mbs_waggon ADD CONSTRAINT FK_53370B15AB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE mbs_waggon ADD CONSTRAINT FK_53370B15248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
    }
}
