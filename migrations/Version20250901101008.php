<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901101008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_category ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_category ADD CONSTRAINT FK_166BF2E4A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_166BF2E4A76ED395 ON mbs_category (user_id)');
        $this->addSql('ALTER TABLE mbs_company ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_company ADD CONSTRAINT FK_B22E76D5A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_B22E76D5A76ED395 ON mbs_company (user_id)');
        $this->addSql('ALTER TABLE mbs_dealer ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_dealer ADD CONSTRAINT FK_E425D918A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_E425D918A76ED395 ON mbs_dealer (user_id)');
        $this->addSql('ALTER TABLE mbs_epoch ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_epoch ADD CONSTRAINT FK_8D6CA0BDA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_8D6CA0BDA76ED395 ON mbs_epoch (user_id)');
        $this->addSql('ALTER TABLE mbs_maker ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_maker ADD CONSTRAINT FK_F9882DA6A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_F9882DA6A76ED395 ON mbs_maker (user_id)');
        $this->addSql('ALTER TABLE mbs_manufacturer ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_manufacturer ADD CONSTRAINT FK_C8232490A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_C8232490A76ED395 ON mbs_manufacturer (user_id)');
        $this->addSql('ALTER TABLE mbs_project ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_project ADD CONSTRAINT FK_D222AF74A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_D222AF74A76ED395 ON mbs_project (user_id)');
        $this->addSql('ALTER TABLE mbs_scale ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_scale ADD CONSTRAINT FK_D3D77796A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_D3D77796A76ED395 ON mbs_scale (user_id)');
        $this->addSql('ALTER TABLE mbs_scale_track ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_scale_track ADD CONSTRAINT FK_E85AD7F2A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_E85AD7F2A76ED395 ON mbs_scale_track (user_id)');
        $this->addSql('ALTER TABLE mbs_storage ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_storage ADD CONSTRAINT FK_A9EB64AEA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_A9EB64AEA76ED395 ON mbs_storage (user_id)');
        $this->addSql('ALTER TABLE mbs_subcategory ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_subcategory ADD CONSTRAINT FK_2BDABF1FA76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_2BDABF1FA76ED395 ON mbs_subcategory (user_id)');
        $this->addSql('ALTER TABLE mbs_subepoch ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_subepoch ADD CONSTRAINT FK_D869F650A76ED395 FOREIGN KEY (user_id) REFERENCES mbs_user (id)');
        $this->addSql('CREATE INDEX IDX_D869F650A76ED395 ON mbs_subepoch (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_company DROP FOREIGN KEY FK_B22E76D5A76ED395');
        $this->addSql('DROP INDEX IDX_B22E76D5A76ED395 ON mbs_company');
        $this->addSql('ALTER TABLE mbs_company DROP user_id');
        $this->addSql('ALTER TABLE mbs_project DROP FOREIGN KEY FK_D222AF74A76ED395');
        $this->addSql('DROP INDEX IDX_D222AF74A76ED395 ON mbs_project');
        $this->addSql('ALTER TABLE mbs_project DROP user_id');
        $this->addSql('ALTER TABLE mbs_storage DROP FOREIGN KEY FK_A9EB64AEA76ED395');
        $this->addSql('DROP INDEX IDX_A9EB64AEA76ED395 ON mbs_storage');
        $this->addSql('ALTER TABLE mbs_storage DROP user_id');
        $this->addSql('ALTER TABLE mbs_maker DROP FOREIGN KEY FK_F9882DA6A76ED395');
        $this->addSql('DROP INDEX IDX_F9882DA6A76ED395 ON mbs_maker');
        $this->addSql('ALTER TABLE mbs_maker DROP user_id');
        $this->addSql('ALTER TABLE mbs_scale_track DROP FOREIGN KEY FK_E85AD7F2A76ED395');
        $this->addSql('DROP INDEX IDX_E85AD7F2A76ED395 ON mbs_scale_track');
        $this->addSql('ALTER TABLE mbs_scale_track DROP user_id');
        $this->addSql('ALTER TABLE mbs_subepoch DROP FOREIGN KEY FK_D869F650A76ED395');
        $this->addSql('DROP INDEX IDX_D869F650A76ED395 ON mbs_subepoch');
        $this->addSql('ALTER TABLE mbs_subepoch DROP user_id');
        $this->addSql('ALTER TABLE mbs_subcategory DROP FOREIGN KEY FK_2BDABF1FA76ED395');
        $this->addSql('DROP INDEX IDX_2BDABF1FA76ED395 ON mbs_subcategory');
        $this->addSql('ALTER TABLE mbs_subcategory DROP user_id');
        $this->addSql('ALTER TABLE mbs_epoch DROP FOREIGN KEY FK_8D6CA0BDA76ED395');
        $this->addSql('DROP INDEX IDX_8D6CA0BDA76ED395 ON mbs_epoch');
        $this->addSql('ALTER TABLE mbs_epoch DROP user_id');
        $this->addSql('ALTER TABLE mbs_scale DROP FOREIGN KEY FK_D3D77796A76ED395');
        $this->addSql('DROP INDEX IDX_D3D77796A76ED395 ON mbs_scale');
        $this->addSql('ALTER TABLE mbs_scale DROP user_id');
        $this->addSql('ALTER TABLE mbs_dealer DROP FOREIGN KEY FK_E425D918A76ED395');
        $this->addSql('DROP INDEX IDX_E425D918A76ED395 ON mbs_dealer');
        $this->addSql('ALTER TABLE mbs_dealer DROP user_id');
        $this->addSql('ALTER TABLE mbs_manufacturer DROP FOREIGN KEY FK_C8232490A76ED395');
        $this->addSql('DROP INDEX IDX_C8232490A76ED395 ON mbs_manufacturer');
        $this->addSql('ALTER TABLE mbs_manufacturer DROP user_id');
        $this->addSql('ALTER TABLE mbs_category DROP FOREIGN KEY FK_166BF2E4A76ED395');
        $this->addSql('DROP INDEX IDX_166BF2E4A76ED395 ON mbs_category');
        $this->addSql('ALTER TABLE mbs_category DROP user_id');
    }
}
