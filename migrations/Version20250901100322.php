<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901100322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_status ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_status ADD CONSTRAINT FK_88868506A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_88868506A76ED395 ON mbs_status (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_status DROP FOREIGN KEY FK_88868506A76ED395');
        $this->addSql('DROP INDEX IDX_88868506A76ED395 ON mbs_status');
        $this->addSql('ALTER TABLE mbs_status DROP user_id');
    }
}
