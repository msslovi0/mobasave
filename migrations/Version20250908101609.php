<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908101609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_company ADD image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE mbs_dealer ADD image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE mbs_manufacturer ADD image VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_company DROP image');
        $this->addSql('ALTER TABLE mbs_dealer DROP image');
        $this->addSql('ALTER TABLE mbs_manufacturer DROP image');
    }
}
