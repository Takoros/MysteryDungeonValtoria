<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230718014327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE raid (id VARCHAR(255) NOT NULL, area_id INT NOT NULL, name VARCHAR(255) NOT NULL, enter_min_level INT NOT NULL, room_numbers INT NOT NULL, description VARCHAR(255) NOT NULL, rooms LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_578763B3BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE raid_instance (id INT AUTO_INCREMENT NOT NULL, raid_id VARCHAR(255) NOT NULL, leader_id INT NOT NULL, status VARCHAR(255) NOT NULL, current_explorers_room INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1F47438D9C55ABC9 (raid_id), UNIQUE INDEX UNIQ_1F47438D73154ED4 (leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE raid ADD CONSTRAINT FK_578763B3BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE raid_instance ADD CONSTRAINT FK_1F47438D9C55ABC9 FOREIGN KEY (raid_id) REFERENCES raid (id)');
        $this->addSql('ALTER TABLE raid_instance ADD CONSTRAINT FK_1F47438D73154ED4 FOREIGN KEY (leader_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE `character` ADD current_exploration_raid_instance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB03428D43544 FOREIGN KEY (current_exploration_raid_instance_id) REFERENCES raid_instance (id)');
        $this->addSql('CREATE INDEX IDX_937AB03428D43544 ON `character` (current_exploration_raid_instance_id)');
        $this->addSql('ALTER TABLE combat_log ADD raid_instance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE combat_log ADD CONSTRAINT FK_21AD4505BC408509 FOREIGN KEY (raid_instance_id) REFERENCES raid_instance (id)');
        $this->addSql('CREATE INDEX IDX_21AD4505BC408509 ON combat_log (raid_instance_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB03428D43544');
        $this->addSql('ALTER TABLE combat_log DROP FOREIGN KEY FK_21AD4505BC408509');
        $this->addSql('ALTER TABLE raid DROP FOREIGN KEY FK_578763B3BD0F409C');
        $this->addSql('ALTER TABLE raid_instance DROP FOREIGN KEY FK_1F47438D9C55ABC9');
        $this->addSql('ALTER TABLE raid_instance DROP FOREIGN KEY FK_1F47438D73154ED4');
        $this->addSql('DROP TABLE raid');
        $this->addSql('DROP TABLE raid_instance');
        $this->addSql('DROP INDEX IDX_937AB03428D43544 ON `character`');
        $this->addSql('ALTER TABLE `character` DROP current_exploration_raid_instance_id');
        $this->addSql('DROP INDEX IDX_21AD4505BC408509 ON combat_log');
        $this->addSql('ALTER TABLE combat_log DROP raid_instance_id');
    }
}
