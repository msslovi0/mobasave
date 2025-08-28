<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828140754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_company (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, logo VARBINARY(255) NOT NULL, vector VARBINARY(255) NOT NULL, INDEX IDX_B22E76D5F92F3E70 (country_id), INDEX IDX_B22E76D55D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_company ADD CONSTRAINT FK_B22E76D5F92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('ALTER TABLE mbs_company ADD CONSTRAINT FK_B22E76D55D83CC1 FOREIGN KEY (state_id) REFERENCES mbs_state (id)');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F5D83CC1');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FF92F3E70');
        $this->addSql('DROP TABLE company');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, logo VARBINARY(255) NOT NULL, vector VARBINARY(255) NOT NULL, INDEX IDX_4FBF094FF92F3E70 (country_id), INDEX IDX_4FBF094F5D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F5D83CC1 FOREIGN KEY (state_id) REFERENCES mbs_state (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('ALTER TABLE mbs_company DROP FOREIGN KEY FK_B22E76D5F92F3E70');
        $this->addSql('ALTER TABLE mbs_company DROP FOREIGN KEY FK_B22E76D55D83CC1');
        $this->addSql('DROP TABLE mbs_company');
    }
}
