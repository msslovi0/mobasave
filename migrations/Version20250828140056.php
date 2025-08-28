<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828140056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_manufacturer CHANGE abbr2 abbr2 VARCHAR(2) DEFAULT NULL, CHANGE abbr3 abbr3 VARCHAR(3) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_ABBR2 ON mbs_manufacturer (abbr2)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_ABBR3 ON mbs_manufacturer (abbr3)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_ABBR2 ON mbs_manufacturer');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_ABBR3 ON mbs_manufacturer');
        $this->addSql('ALTER TABLE mbs_manufacturer CHANGE abbr2 abbr2 VARCHAR(2) NOT NULL, CHANGE abbr3 abbr3 VARCHAR(3) NOT NULL');
    }
}
