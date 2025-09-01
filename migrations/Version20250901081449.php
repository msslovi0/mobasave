<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901081449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model ADD box LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD instructions TINYINT(1) NOT NULL, ADD parts TINYINT(1) NOT NULL, ADD displaycase TINYINT(1) NOT NULL, ADD weathered TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP box, DROP instructions, DROP parts, DROP displaycase, DROP weathered');
    }
}
