<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925123020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_document (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, document_type_id INT DEFAULT NULL, file VARCHAR(255) NOT NULL, INDEX IDX_C84E61537975B7E7 (model_id), INDEX IDX_C84E615361232A4F (document_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_document_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_document ADD CONSTRAINT FK_C84E61537975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_document ADD CONSTRAINT FK_C84E615361232A4F FOREIGN KEY (document_type_id) REFERENCES mbs_document_type (id)');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7661232A4F');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A767975B7E7');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, model_id INT DEFAULT NULL, document_type_id INT DEFAULT NULL, file VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_D8698A767975B7E7 (model_id), INDEX IDX_D8698A7661232A4F (document_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE document_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7661232A4F FOREIGN KEY (document_type_id) REFERENCES document_type (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A767975B7E7 FOREIGN KEY (model_id) REFERENCES mbs_model (id)');
        $this->addSql('ALTER TABLE mbs_document DROP FOREIGN KEY FK_C84E61537975B7E7');
        $this->addSql('ALTER TABLE mbs_document DROP FOREIGN KEY FK_C84E615361232A4F');
        $this->addSql('DROP TABLE mbs_document');
        $this->addSql('DROP TABLE mbs_document_type');
    }
}
