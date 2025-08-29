<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829081749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_database (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, color VARCHAR(10) DEFAULT NULL, INDEX IDX_D974ED0BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_decoderfuntion (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sound VARBINARY(255) NOT NULL, light VARBINARY(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_digital (id INT AUTO_INCREMENT NOT NULL, address INT DEFAULT NULL, interface VARCHAR(255) DEFAULT NULL, decoder VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_functionkey (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_database ADD CONSTRAINT FK_D974ED0BA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_database DROP FOREIGN KEY FK_D974ED0BA76ED395');
        $this->addSql('DROP TABLE mbs_database');
        $this->addSql('DROP TABLE mbs_decoderfuntion');
        $this->addSql('DROP TABLE mbs_digital');
        $this->addSql('DROP TABLE mbs_functionkey');
    }
}
