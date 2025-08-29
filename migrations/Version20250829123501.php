<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829123501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_car ADD import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_container ADD import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_locomotive ADD import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_tram ADD import VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE mbs_vehicle ADD import VARCHAR(10) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_locomotive DROP import');
        $this->addSql('ALTER TABLE mbs_vehicle DROP import');
        $this->addSql('ALTER TABLE mbs_container DROP import');
        $this->addSql('ALTER TABLE mbs_tram DROP import');
        $this->addSql('ALTER TABLE mbs_car DROP import');
    }
}
