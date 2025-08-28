<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828144441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maker (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maker_category (maker_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_7D836AB868DA5EC3 (maker_id), INDEX IDX_7D836AB812469DE2 (category_id), PRIMARY KEY(maker_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_waggon (id INT AUTO_INCREMENT NOT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) DEFAULT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_53370B15AB4FC384 (power_id), INDEX IDX_53370B15248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE maker_category ADD CONSTRAINT FK_7D836AB868DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_category ADD CONSTRAINT FK_7D836AB812469DE2 FOREIGN KEY (category_id) REFERENCES mbs_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mbs_waggon ADD CONSTRAINT FK_53370B15AB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE mbs_waggon ADD CONSTRAINT FK_53370B15248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
        $this->addSql('ALTER TABLE waggon DROP FOREIGN KEY FK_A0B1EB0F248A9586');
        $this->addSql('ALTER TABLE waggon DROP FOREIGN KEY FK_A0B1EB0FAB4FC384');
        $this->addSql('DROP TABLE waggon');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE waggon (id INT AUTO_INCREMENT NOT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, registration VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_A0B1EB0FAB4FC384 (power_id), INDEX IDX_A0B1EB0F248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE waggon ADD CONSTRAINT FK_A0B1EB0F248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
        $this->addSql('ALTER TABLE waggon ADD CONSTRAINT FK_A0B1EB0FAB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE maker_category DROP FOREIGN KEY FK_7D836AB868DA5EC3');
        $this->addSql('ALTER TABLE maker_category DROP FOREIGN KEY FK_7D836AB812469DE2');
        $this->addSql('ALTER TABLE mbs_waggon DROP FOREIGN KEY FK_53370B15AB4FC384');
        $this->addSql('ALTER TABLE mbs_waggon DROP FOREIGN KEY FK_53370B15248A9586');
        $this->addSql('DROP TABLE maker');
        $this->addSql('DROP TABLE maker_category');
        $this->addSql('DROP TABLE mbs_waggon');
    }
}
