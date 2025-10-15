<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251014143855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_containertype ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_coupler ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_decoder ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_edition ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_pininterface ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_power ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE mbs_protocol ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_pininterface DROP uuid');
        $this->addSql('ALTER TABLE mbs_coupler DROP uuid');
        $this->addSql('ALTER TABLE mbs_containertype DROP uuid');
        $this->addSql('ALTER TABLE mbs_edition DROP uuid');
        $this->addSql('ALTER TABLE mbs_power DROP uuid');
        $this->addSql('ALTER TABLE mbs_decoder DROP uuid');
        $this->addSql('ALTER TABLE mbs_protocol DROP uuid');
    }
}
