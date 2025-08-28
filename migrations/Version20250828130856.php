<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828130856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, logo VARBINARY(255) NOT NULL, vector VARBINARY(255) NOT NULL, INDEX IDX_4FBF094FF92F3E70 (country_id), INDEX IDX_4FBF094F5D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_dealer (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, extra VARCHAR(255) DEFAULT NULL, zip VARCHAR(15) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, youtube VARCHAR(255) DEFAULT NULL, tiktok VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, logo VARBINARY(255) NOT NULL, vector VARBINARY(255) NOT NULL, INDEX IDX_E425D918F92F3E70 (country_id), INDEX IDX_E425D9185D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_manufacturer (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, state_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, extra VARCHAR(255) DEFAULT NULL, zip VARCHAR(15) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, youtube VARCHAR(255) DEFAULT NULL, tiktok VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, logo VARBINARY(255) NOT NULL, vector VARBINARY(255) NOT NULL, abbr2 VARCHAR(2) NOT NULL, abbr3 VARCHAR(3) NOT NULL, INDEX IDX_C8232490F92F3E70 (country_id), INDEX IDX_C82324905D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F5D83CC1 FOREIGN KEY (state_id) REFERENCES mbs_state (id)');
        $this->addSql('ALTER TABLE mbs_dealer ADD CONSTRAINT FK_E425D918F92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('ALTER TABLE mbs_dealer ADD CONSTRAINT FK_E425D9185D83CC1 FOREIGN KEY (state_id) REFERENCES mbs_state (id)');
        $this->addSql('ALTER TABLE mbs_manufacturer ADD CONSTRAINT FK_C8232490F92F3E70 FOREIGN KEY (country_id) REFERENCES mbs_country (id)');
        $this->addSql('ALTER TABLE mbs_manufacturer ADD CONSTRAINT FK_C82324905D83CC1 FOREIGN KEY (state_id) REFERENCES mbs_state (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FF92F3E70');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F5D83CC1');
        $this->addSql('ALTER TABLE mbs_dealer DROP FOREIGN KEY FK_E425D918F92F3E70');
        $this->addSql('ALTER TABLE mbs_dealer DROP FOREIGN KEY FK_E425D9185D83CC1');
        $this->addSql('ALTER TABLE mbs_manufacturer DROP FOREIGN KEY FK_C8232490F92F3E70');
        $this->addSql('ALTER TABLE mbs_manufacturer DROP FOREIGN KEY FK_C82324905D83CC1');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE mbs_dealer');
        $this->addSql('DROP TABLE mbs_manufacturer');
    }
}
