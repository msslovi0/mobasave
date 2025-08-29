<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829090452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE digital_function (id INT AUTO_INCREMENT NOT NULL, digital_id INT NOT NULL, functionkey_id INT NOT NULL, decoderfunction_id INT NOT NULL, INDEX IDX_2A5B7DF8D3571EA4 (digital_id), INDEX IDX_2A5B7DF86BDC5EC2 (functionkey_id), INDEX IDX_2A5B7DF848539B6B (decoderfunction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE digital_function ADD CONSTRAINT FK_2A5B7DF8D3571EA4 FOREIGN KEY (digital_id) REFERENCES mbs_digital (id)');
        $this->addSql('ALTER TABLE digital_function ADD CONSTRAINT FK_2A5B7DF86BDC5EC2 FOREIGN KEY (functionkey_id) REFERENCES mbs_functionkey (id)');
        $this->addSql('ALTER TABLE digital_function ADD CONSTRAINT FK_2A5B7DF848539B6B FOREIGN KEY (decoderfunction_id) REFERENCES mbs_decoderfuntion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_function DROP FOREIGN KEY FK_2A5B7DF8D3571EA4');
        $this->addSql('ALTER TABLE digital_function DROP FOREIGN KEY FK_2A5B7DF86BDC5EC2');
        $this->addSql('ALTER TABLE digital_function DROP FOREIGN KEY FK_2A5B7DF848539B6B');
        $this->addSql('DROP TABLE digital_function');
    }
}
