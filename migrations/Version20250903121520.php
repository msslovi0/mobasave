<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903121520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_car CHANGE import import VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_container CHANGE import import VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_locomotive CHANGE import import VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_tram CHANGE import import VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_vehicle CHANGE import import VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_locomotive CHANGE import import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_vehicle CHANGE import import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_container CHANGE import import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_tram CHANGE import import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_car CHANGE import import VARCHAR(10) NOT NULL');
    }
}
