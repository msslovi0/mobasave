<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921125049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_storage ADD country_id INT DEFAULT NULL, ADD fill INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_storage ADD CONSTRAINT FK_A9EB64AEF92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('CREATE INDEX IDX_A9EB64AEF92F3E70 ON mbs_storage (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_storage DROP FOREIGN KEY FK_A9EB64AEF92F3E70');
        $this->addSql('DROP INDEX IDX_A9EB64AEF92F3E70 ON mbs_storage');
        $this->addSql('ALTER TABLE mbs_storage DROP country_id, DROP fill');
    }
}
