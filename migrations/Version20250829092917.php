<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829092917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_locomotive (id INT AUTO_INCREMENT NOT NULL, maker_id INT DEFAULT NULL, axle_id INT DEFAULT NULL, power_id INT DEFAULT NULL, coupler_id INT DEFAULT NULL, class VARCHAR(255) NOT NULL, registration VARCHAR(255) DEFAULT NULL, length DOUBLE PRECISION DEFAULT NULL, digital VARBINARY(255) NOT NULL, sound VARBINARY(255) NOT NULL, smoke VARBINARY(255) NOT NULL, dccready VARBINARY(255) NOT NULL, INDEX IDX_1FD4D24268DA5EC3 (maker_id), INDEX IDX_1FD4D2429EE696DC (axle_id), INDEX IDX_1FD4D242AB4FC384 (power_id), INDEX IDX_1FD4D242248A9586 (coupler_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mbs_model (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, subcategory_id INT DEFAULT NULL, manufacturer_id INT DEFAULT NULL, company_id INT DEFAULT NULL, scale_id INT DEFAULT NULL, track_id INT DEFAULT NULL, epoch_id INT DEFAULT NULL, subepoch_id INT DEFAULT NULL, storage_id INT DEFAULT NULL, project_id INT DEFAULT NULL, dealer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, model VARCHAR(255) DEFAULT NULL, gtin13 VARCHAR(13) DEFAULT NULL, color1 VARCHAR(10) DEFAULT NULL, color2 VARCHAR(10) DEFAULT NULL, color3 VARCHAR(10) DEFAULT NULL, quantity INT NOT NULL, purchased DATETIME DEFAULT NULL, msrp DOUBLE PRECISION DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_E80420CB12469DE2 (category_id), INDEX IDX_E80420CB5DC6FE57 (subcategory_id), INDEX IDX_E80420CBA23B42D (manufacturer_id), INDEX IDX_E80420CB979B1AD6 (company_id), INDEX IDX_E80420CBF73142C2 (scale_id), INDEX IDX_E80420CB5ED23C43 (track_id), INDEX IDX_E80420CB51E3D241 (epoch_id), INDEX IDX_E80420CB5A736FC8 (subepoch_id), INDEX IDX_E80420CB5CC5DB90 (storage_id), INDEX IDX_E80420CB166D1F9C (project_id), INDEX IDX_E80420CB249E6EA1 (dealer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mbs_locomotive ADD CONSTRAINT FK_1FD4D24268DA5EC3 FOREIGN KEY (maker_id) REFERENCES mbs_maker (id)');
        $this->addSql('ALTER TABLE mbs_locomotive ADD CONSTRAINT FK_1FD4D2429EE696DC FOREIGN KEY (axle_id) REFERENCES mbs_axle (id)');
        $this->addSql('ALTER TABLE mbs_locomotive ADD CONSTRAINT FK_1FD4D242AB4FC384 FOREIGN KEY (power_id) REFERENCES mbs_power (id)');
        $this->addSql('ALTER TABLE mbs_locomotive ADD CONSTRAINT FK_1FD4D242248A9586 FOREIGN KEY (coupler_id) REFERENCES mbs_coupler (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB12469DE2 FOREIGN KEY (category_id) REFERENCES mbs_category (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB5DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES mbs_subcategory (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBA23B42D FOREIGN KEY (manufacturer_id) REFERENCES mbs_manufacturer (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB979B1AD6 FOREIGN KEY (company_id) REFERENCES mbs_company (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBF73142C2 FOREIGN KEY (scale_id) REFERENCES mbs_scale (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB5ED23C43 FOREIGN KEY (track_id) REFERENCES mbs_scale_track (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB51E3D241 FOREIGN KEY (epoch_id) REFERENCES mbs_epoch (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB5A736FC8 FOREIGN KEY (subepoch_id) REFERENCES mbs_subepoch (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB5CC5DB90 FOREIGN KEY (storage_id) REFERENCES mbs_storage (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB166D1F9C FOREIGN KEY (project_id) REFERENCES mbs_project (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB249E6EA1 FOREIGN KEY (dealer_id) REFERENCES mbs_dealer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_locomotive DROP FOREIGN KEY FK_1FD4D24268DA5EC3');
        $this->addSql('ALTER TABLE mbs_locomotive DROP FOREIGN KEY FK_1FD4D2429EE696DC');
        $this->addSql('ALTER TABLE mbs_locomotive DROP FOREIGN KEY FK_1FD4D242AB4FC384');
        $this->addSql('ALTER TABLE mbs_locomotive DROP FOREIGN KEY FK_1FD4D242248A9586');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB12469DE2');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB5DC6FE57');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBA23B42D');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB979B1AD6');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBF73142C2');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB5ED23C43');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB51E3D241');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB5A736FC8');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB5CC5DB90');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB166D1F9C');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB249E6EA1');
        $this->addSql('DROP TABLE mbs_locomotive');
        $this->addSql('DROP TABLE mbs_model');
    }
}
