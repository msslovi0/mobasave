<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015092409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_epoch ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_scale ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_scale_track ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_subepoch ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_scale_track DROP uuid');
        $this->addSql('ALTER TABLE mbs_subepoch DROP uuid');
        $this->addSql('ALTER TABLE mbs_epoch DROP uuid');
        $this->addSql('ALTER TABLE mbs_scale DROP uuid');
    }
}
