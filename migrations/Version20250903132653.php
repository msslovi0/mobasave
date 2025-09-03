<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903132653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324DCCD59258 FOREIGN KEY (protocol_id) REFERENCES mbs_protocol (id)');
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324D94695287 FOREIGN KEY (decoder_id) REFERENCES mbs_decoder (id)');
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324DD7CF5021 FOREIGN KEY (pininterface_id) REFERENCES mbs_pininterface (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324DCCD59258');
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324D94695287');
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324DD7CF5021');
    }
}
