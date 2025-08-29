<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829090311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mbs_decoderfuntion DROP FOREIGN KEY FK_BC62BCDCBBBE68B5');
        $this->addSql('ALTER TABLE mbs_functionkey DROP FOREIGN KEY FK_ADD3FDCFBBBE68B5');
        $this->addSql('ALTER TABLE mbs_digital_function DROP FOREIGN KEY FK_E023412BD3571EA4');
        $this->addSql('DROP TABLE mbs_digital_function');
        $this->addSql('DROP INDEX IDX_BC62BCDCBBBE68B5 ON mbs_decoderfuntion');
        $this->addSql('ALTER TABLE mbs_decoderfuntion DROP digital_function_id');
        $this->addSql('DROP INDEX IDX_ADD3FDCFBBBE68B5 ON mbs_functionkey');
        $this->addSql('ALTER TABLE mbs_functionkey DROP digital_function_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mbs_digital_function (id INT AUTO_INCREMENT NOT NULL, digital_id INT NOT NULL, INDEX IDX_E023412BD3571EA4 (digital_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE mbs_digital_function ADD CONSTRAINT FK_E023412BD3571EA4 FOREIGN KEY (digital_id) REFERENCES mbs_digital (id)');
        $this->addSql('ALTER TABLE mbs_decoderfuntion ADD digital_function_id INT NOT NULL');
        $this->addSql('ALTER TABLE mbs_decoderfuntion ADD CONSTRAINT FK_BC62BCDCBBBE68B5 FOREIGN KEY (digital_function_id) REFERENCES mbs_digital_function (id)');
        $this->addSql('CREATE INDEX IDX_BC62BCDCBBBE68B5 ON mbs_decoderfuntion (digital_function_id)');
        $this->addSql('ALTER TABLE mbs_functionkey ADD digital_function_id INT NOT NULL');
        $this->addSql('ALTER TABLE mbs_functionkey ADD CONSTRAINT FK_ADD3FDCFBBBE68B5 FOREIGN KEY (digital_function_id) REFERENCES mbs_digital_function (id)');
        $this->addSql('CREATE INDEX IDX_ADD3FDCFBBBE68B5 ON mbs_functionkey (digital_function_id)');
    }
}
