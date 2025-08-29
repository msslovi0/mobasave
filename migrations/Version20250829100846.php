<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829100846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model ADD modeldatabase_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBFADEF234 FOREIGN KEY (modeldatabase_id) REFERENCES mbs_database (id)');
        $this->addSql('CREATE INDEX IDX_E80420CBFADEF234 ON mbs_model (modeldatabase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBFADEF234');
        $this->addSql('DROP INDEX IDX_E80420CBFADEF234 ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model DROP modeldatabase_id');
    }
}
