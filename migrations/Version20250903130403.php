<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903130403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_modelload (id INT AUTO_INCREMENT NOT NULL, model_id INT NOT NULL, loaditem_id INT NOT NULL, INDEX IDX_76BA64B47975B7E7 (model_id), INDEX IDX_76BA64B4811F5C68 (loaditem_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_modelload ADD CONSTRAINT FK_76BA64B47975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_modelload ADD CONSTRAINT FK_76BA64B4811F5C68 FOREIGN KEY (loaditem_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE modelload DROP FOREIGN KEY FK_3DAE97187975B7E7');
        $this->addSql('ALTER TABLE modelload DROP FOREIGN KEY FK_3DAE9718811F5C68');
        $this->addSql('ALTER TABLE mbs_model_loaditem DROP FOREIGN KEY FK_EDA1A6BE811F5C68');
        $this->addSql('ALTER TABLE mbs_model_loaditem DROP FOREIGN KEY FK_EDA1A6BE7975B7E7');
        $this->addSql('DROP TABLE modelload');
        $this->addSql('DROP TABLE mbs_model_loaditem');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE modelload (id INT AUTO_INCREMENT NOT NULL, model_id INT NOT NULL, loaditem_id INT NOT NULL, INDEX IDX_3DAE97187975B7E7 (model_id), INDEX IDX_3DAE9718811F5C68 (loaditem_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mbs_model_loaditem (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, loaditem_id INT DEFAULT NULL, INDEX IDX_EDA1A6BE811F5C68 (loaditem_id), INDEX IDX_EDA1A6BE7975B7E7 (model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE modelload ADD CONSTRAINT FK_3DAE97187975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE modelload ADD CONSTRAINT FK_3DAE9718811F5C68 FOREIGN KEY (loaditem_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_model_loaditem ADD CONSTRAINT FK_EDA1A6BE811F5C68 FOREIGN KEY (loaditem_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_model_loaditem ADD CONSTRAINT FK_EDA1A6BE7975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_modelload DROP FOREIGN KEY FK_76BA64B47975B7E7');
        $this->addSql('ALTER TABLE mbs_modelload DROP FOREIGN KEY FK_76BA64B4811F5C68');
        $this->addSql('DROP TABLE mbs_modelload');
    }
}
