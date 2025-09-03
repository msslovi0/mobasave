<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903132303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE decoder (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_EE242FA7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pininterface (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_69D52396A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE protocol (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C8C0BC4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE decoder ADD CONSTRAINT FK_EE242FA7A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('ALTER TABLE pininterface ADD CONSTRAINT FK_69D52396A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('ALTER TABLE protocol ADD CONSTRAINT FK_C8C0BC4CA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('ALTER TABLE mbs_digital ADD protocol_id INT DEFAULT NULL, ADD decoder_id INT DEFAULT NULL, ADD pininterface_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324DCCD59258 FOREIGN KEY (protocol_id) REFERENCES protocol (id)');
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324D94695287 FOREIGN KEY (decoder_id) REFERENCES decoder (id)');
        $this->addSql('ALTER TABLE mbs_digital ADD CONSTRAINT FK_36D9324DD7CF5021 FOREIGN KEY (pininterface_id) REFERENCES pininterface (id)');
        $this->addSql('CREATE INDEX IDX_36D9324DCCD59258 ON mbs_digital (protocol_id)');
        $this->addSql('CREATE INDEX IDX_36D9324D94695287 ON mbs_digital (decoder_id)');
        $this->addSql('CREATE INDEX IDX_36D9324DD7CF5021 ON mbs_digital (pininterface_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324D94695287');
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324DD7CF5021');
        $this->addSql('ALTER TABLE mbs_digital DROP FOREIGN KEY FK_36D9324DCCD59258');
        $this->addSql('ALTER TABLE decoder DROP FOREIGN KEY FK_EE242FA7A76ED395');
        $this->addSql('ALTER TABLE pininterface DROP FOREIGN KEY FK_69D52396A76ED395');
        $this->addSql('ALTER TABLE protocol DROP FOREIGN KEY FK_C8C0BC4CA76ED395');
        $this->addSql('DROP TABLE decoder');
        $this->addSql('DROP TABLE pininterface');
        $this->addSql('DROP TABLE protocol');
        $this->addSql('DROP INDEX IDX_36D9324DCCD59258 ON mbs_digital');
        $this->addSql('DROP INDEX IDX_36D9324D94695287 ON mbs_digital');
        $this->addSql('DROP INDEX IDX_36D9324DD7CF5021 ON mbs_digital');
        $this->addSql('ALTER TABLE mbs_digital DROP protocol_id, DROP decoder_id, DROP pininterface_id');
    }
}
