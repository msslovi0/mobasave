<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902143313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_containertype ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_containertype ADD CONSTRAINT FK_4D900372A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_4D900372A76ED395 ON mbs_containertype (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_containertype DROP FOREIGN KEY FK_4D900372A76ED395');
        $this->addSql('DROP INDEX IDX_4D900372A76ED395 ON mbs_containertype');
        $this->addSql('ALTER TABLE mbs_containertype DROP user_id');
    }
}
