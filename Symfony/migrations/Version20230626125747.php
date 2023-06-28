<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230626125747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dungeon_instance (id INT AUTO_INCREMENT NOT NULL, dungeon_id VARCHAR(255) NOT NULL, leader_id INT DEFAULT NULL, date_created DATE NOT NULL, content LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_36DC45E8B606863 (dungeon_id), UNIQUE INDEX UNIQ_36DC45E873154ED4 (leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dungeon ADD max_monster_level INT NOT NULL, ADD min_monster_level INT NOT NULL, ADD size VARCHAR(255) NOT NULL, ADD monster_living_list LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', DROP json, CHANGE id id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE dungeon_instance ADD CONSTRAINT FK_36DC45E8B606863 FOREIGN KEY (dungeon_id) REFERENCES dungeon (id)');
        $this->addSql('ALTER TABLE dungeon_instance ADD CONSTRAINT FK_36DC45E873154ED4 FOREIGN KEY (leader_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE area ADD is_explorable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE `character` ADD current_exploration_dungeon_instance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034A83E8643 FOREIGN KEY (current_exploration_dungeon_instance_id) REFERENCES dungeon_instance (id)');
        $this->addSql('CREATE INDEX IDX_937AB034A83E8643 ON `character` (current_exploration_dungeon_instance_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034A83E8643');
        $this->addSql('ALTER TABLE dungeon_instance DROP FOREIGN KEY FK_36DC45E8B606863');
        $this->addSql('ALTER TABLE dungeon_instance DROP FOREIGN KEY FK_36DC45E873154ED4');
        $this->addSql('DROP TABLE dungeon_instance');
        $this->addSql('ALTER TABLE area DROP is_explorable');
        $this->addSql('DROP INDEX IDX_937AB034A83E8643 ON `character`');
        $this->addSql('ALTER TABLE `character` DROP current_exploration_dungeon_instance_id');
        $this->addSql('ALTER TABLE dungeon ADD json VARCHAR(50) DEFAULT NULL, DROP max_monster_level, DROP min_monster_level, DROP size, DROP monster_living_list, CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
