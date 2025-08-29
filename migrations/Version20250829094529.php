<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829094529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_model_loaditem (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, loaditem_id INT DEFAULT NULL, INDEX IDX_EDA1A6BE7975B7E7 (model_id), INDEX IDX_EDA1A6BE811F5C68 (loaditem_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_model_loaditem ADD CONSTRAINT FK_EDA1A6BE7975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_model_loaditem ADD CONSTRAINT FK_EDA1A6BE811F5C68 FOREIGN KEY (loaditem_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE model_loaditem DROP FOREIGN KEY FK_C407F0027975B7E7');
        $this->addSql('ALTER TABLE model_loaditem DROP FOREIGN KEY FK_C407F002811F5C68');
        $this->addSql('DROP TABLE model_loaditem');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE model_loaditem (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, loaditem_id INT DEFAULT NULL, INDEX IDX_C407F0027975B7E7 (model_id), INDEX IDX_C407F002811F5C68 (loaditem_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE model_loaditem ADD CONSTRAINT FK_C407F0027975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE model_loaditem ADD CONSTRAINT FK_C407F002811F5C68 FOREIGN KEY (loaditem_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_model_loaditem DROP FOREIGN KEY FK_EDA1A6BE7975B7E7');
        $this->addSql('ALTER TABLE mbs_model_loaditem DROP FOREIGN KEY FK_EDA1A6BE811F5C68');
        $this->addSql('DROP TABLE mbs_model_loaditem');
    }
}
