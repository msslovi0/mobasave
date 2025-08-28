<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828152847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_container (id INT AUTO_INCREMENT NOT NULL, containertype_id INT DEFAULT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, INDEX IDX_8CB61FB7206E8517 (containertype_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_container ADD CONSTRAINT FK_8CB61FB7206E8517 FOREIGN KEY (containertype_id) REFERENCES mbs_containertype (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_container DROP FOREIGN KEY FK_8CB61FB7206E8517');
        $this->addSql('DROP TABLE mbs_container');
    }
}
