<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925124752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_document_type ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_document_type ADD CONSTRAINT FK_54F5FFC3A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_54F5FFC3A76ED395 ON mbs_document_type (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_document_type DROP FOREIGN KEY FK_54F5FFC3A76ED395');
        $this->addSql('DROP INDEX IDX_54F5FFC3A76ED395 ON mbs_document_type');
        $this->addSql('ALTER TABLE mbs_document_type DROP user_id');
    }
}
