<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903090250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_category CHANGE color color VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_database CHANGE color color VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model CHANGE color1 color1 VARCHAR(30) DEFAULT NULL, CHANGE color2 color2 VARCHAR(30) DEFAULT NULL, CHANGE color3 color3 VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_project CHANGE color color VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_status CHANGE color color VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE mbs_storage CHANGE color color VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_subcategory CHANGE color color VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_project CHANGE color color VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_storage CHANGE color color VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_subcategory CHANGE color color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model CHANGE color1 color1 VARCHAR(10) DEFAULT NULL, CHANGE color2 color2 VARCHAR(10) DEFAULT NULL, CHANGE color3 color3 VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_status CHANGE color color VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_database CHANGE color color VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_category CHANGE color color VARCHAR(7) DEFAULT NULL');
    }
}
