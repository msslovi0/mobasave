<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902141156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_locomotive CHANGE digital digital TINYINT(1) NOT NULL, CHANGE sound sound TINYINT(1) NOT NULL, CHANGE smoke smoke TINYINT(1) NOT NULL, CHANGE dccready dccready TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_locomotive CHANGE digital digital VARBINARY(255) NOT NULL, CHANGE sound sound VARBINARY(255) NOT NULL, CHANGE smoke smoke VARBINARY(255) NOT NULL, CHANGE dccready dccready VARBINARY(255) NOT NULL');
    }
}
