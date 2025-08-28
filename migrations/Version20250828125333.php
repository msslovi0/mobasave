<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828125333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_state ADD country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_state ADD CONSTRAINT FK_9C0280E9F92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('CREATE INDEX IDX_9C0280E9F92F3E70 ON mbs_state (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_state DROP FOREIGN KEY FK_9C0280E9F92F3E70');
        $this->addSql('DROP INDEX IDX_9C0280E9F92F3E70 ON mbs_state');
        $this->addSql('ALTER TABLE mbs_state DROP country_id');
    }
}
