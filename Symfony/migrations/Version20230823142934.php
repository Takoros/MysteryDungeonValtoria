<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823142934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gear ADD weapon_id INT DEFAULT NULL, ADD scarf_id INT DEFAULT NULL, ADD accessory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gear ADD CONSTRAINT FK_B44539B95B82273 FOREIGN KEY (weapon_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE gear ADD CONSTRAINT FK_B44539B3A54C4CF FOREIGN KEY (scarf_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE gear ADD CONSTRAINT FK_B44539B27E8CC78 FOREIGN KEY (accessory_id) REFERENCES item (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B44539B95B82273 ON gear (weapon_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B44539B3A54C4CF ON gear (scarf_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B44539B27E8CC78 ON gear (accessory_id)');
        $this->addSql('ALTER TABLE inventory ADD inventory_size INT NOT NULL');
        $this->addSql('ALTER TABLE item ADD inventory_id INT DEFAULT NULL, ADD description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E9EEA759 ON item (inventory_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gear DROP FOREIGN KEY FK_B44539B95B82273');
        $this->addSql('ALTER TABLE gear DROP FOREIGN KEY FK_B44539B3A54C4CF');
        $this->addSql('ALTER TABLE gear DROP FOREIGN KEY FK_B44539B27E8CC78');
        $this->addSql('DROP INDEX UNIQ_B44539B95B82273 ON gear');
        $this->addSql('DROP INDEX UNIQ_B44539B3A54C4CF ON gear');
        $this->addSql('DROP INDEX UNIQ_B44539B27E8CC78 ON gear');
        $this->addSql('ALTER TABLE gear DROP weapon_id, DROP scarf_id, DROP accessory_id');
        $this->addSql('ALTER TABLE inventory DROP inventory_size');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E9EEA759');
        $this->addSql('DROP INDEX IDX_1F1B251E9EEA759 ON item');
        $this->addSql('ALTER TABLE item DROP inventory_id, DROP description');
    }
}
