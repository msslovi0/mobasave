<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829095527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model ADD digital_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBD3571EA4 FOREIGN KEY (digital_id) REFERENCES mbs_digital (id)');
        $this->addSql('CREATE INDEX IDX_E80420CBD3571EA4 ON mbs_model (digital_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBD3571EA4');
        $this->addSql('DROP INDEX IDX_E80420CBD3571EA4 ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model DROP digital_id');
    }
}
