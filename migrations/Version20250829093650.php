<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829093650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model ADD locomotive_id INT DEFAULT NULL, ADD container_id INT DEFAULT NULL, ADD car_id INT DEFAULT NULL, ADD vehicle_id INT DEFAULT NULL, ADD tram_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB587009A8 FOREIGN KEY (locomotive_id) REFERENCES mbs_locomotive (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBBC21F742 FOREIGN KEY (container_id) REFERENCES mbs_container (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CBC3C6F69F FOREIGN KEY (car_id) REFERENCES mbs_car (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB545317D1 FOREIGN KEY (vehicle_id) REFERENCES mbs_vehicle (id)');
        $this->addSql('ALTER TABLE mbs_model ADD CONSTRAINT FK_E80420CB37BFEA8D FOREIGN KEY (tram_id) REFERENCES mbs_tram (id)');
        $this->addSql('CREATE INDEX IDX_E80420CB587009A8 ON mbs_model (locomotive_id)');
        $this->addSql('CREATE INDEX IDX_E80420CBBC21F742 ON mbs_model (container_id)');
        $this->addSql('CREATE INDEX IDX_E80420CBC3C6F69F ON mbs_model (car_id)');
        $this->addSql('CREATE INDEX IDX_E80420CB545317D1 ON mbs_model (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_E80420CB37BFEA8D ON mbs_model (tram_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB587009A8');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBBC21F742');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CBC3C6F69F');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB545317D1');
        $this->addSql('ALTER TABLE mbs_model DROP FOREIGN KEY FK_E80420CB37BFEA8D');
        $this->addSql('DROP INDEX IDX_E80420CB587009A8 ON mbs_model');
        $this->addSql('DROP INDEX IDX_E80420CBBC21F742 ON mbs_model');
        $this->addSql('DROP INDEX IDX_E80420CBC3C6F69F ON mbs_model');
        $this->addSql('DROP INDEX IDX_E80420CB545317D1 ON mbs_model');
        $this->addSql('DROP INDEX IDX_E80420CB37BFEA8D ON mbs_model');
        $this->addSql('ALTER TABLE mbs_model DROP locomotive_id, DROP container_id, DROP car_id, DROP vehicle_id, DROP tram_id');
    }
}
