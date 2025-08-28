<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828150703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_maker (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_maker_category (maker_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_54253C0468DA5EC3 (maker_id), INDEX IDX_54253C0412469DE2 (category_id), PRIMARY KEY(maker_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_maker_category ADD CONSTRAINT FK_54253C0468DA5EC3 FOREIGN KEY (maker_id) REFERENCES mbs_maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mbs_maker_category ADD CONSTRAINT FK_54253C0412469DE2 FOREIGN KEY (category_id) REFERENCES mbs_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_category DROP FOREIGN KEY FK_7D836AB812469DE2');
        $this->addSql('ALTER TABLE maker_category DROP FOREIGN KEY FK_7D836AB868DA5EC3');
        $this->addSql('DROP TABLE maker');
        $this->addSql('DROP TABLE maker_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maker (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE maker_category (maker_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_7D836AB868DA5EC3 (maker_id), INDEX IDX_7D836AB812469DE2 (category_id), PRIMARY KEY(maker_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE maker_category ADD CONSTRAINT FK_7D836AB812469DE2 FOREIGN KEY (category_id) REFERENCES mbs_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maker_category ADD CONSTRAINT FK_7D836AB868DA5EC3 FOREIGN KEY (maker_id) REFERENCES maker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mbs_maker_category DROP FOREIGN KEY FK_54253C0468DA5EC3');
        $this->addSql('ALTER TABLE mbs_maker_category DROP FOREIGN KEY FK_54253C0412469DE2');
        $this->addSql('DROP TABLE mbs_maker');
        $this->addSql('DROP TABLE mbs_maker_category');
    }
}
