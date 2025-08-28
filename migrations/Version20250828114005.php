<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828114005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_subepoch (id INT AUTO_INCREMENT NOT NULL, epoch_id INT DEFAULT NULL, name VARCHAR(10) NOT NULL, start VARCHAR(4) DEFAULT NULL, end VARCHAR(4) DEFAULT NULL, INDEX IDX_D869F65051E3D241 (epoch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_subepoch ADD CONSTRAINT FK_D869F65051E3D241 FOREIGN KEY (epoch_id) REFERENCES mbs_epoch (id)');
        $this->addSql('ALTER TABLE subepoch DROP FOREIGN KEY FK_C84E1D7551E3D241');
        $this->addSql('DROP TABLE subepoch');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subepoch (id INT AUTO_INCREMENT NOT NULL, epoch_id INT DEFAULT NULL, name VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, start VARCHAR(4) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, end VARCHAR(4) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C84E1D7551E3D241 (epoch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE subepoch ADD CONSTRAINT FK_C84E1D7551E3D241 FOREIGN KEY (epoch_id) REFERENCES mbs_epoch (id)');
        $this->addSql('ALTER TABLE mbs_subepoch DROP FOREIGN KEY FK_D869F65051E3D241');
        $this->addSql('DROP TABLE mbs_subepoch');
    }
}
