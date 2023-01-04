<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230104214119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attack (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(30) NOT NULL, description VARCHAR(200) DEFAULT NULL, power DOUBLE PRECISION NOT NULL, critical_power INT NOT NULL, action_point_cost INT NOT NULL, INDEX IDX_47C02D3BC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, stats_id INT DEFAULT NULL, species_id INT NOT NULL, name VARCHAR(30) NOT NULL, gender VARCHAR(10) NOT NULL, age INT NOT NULL, description VARCHAR(200) DEFAULT NULL, level INT NOT NULL, xp INT NOT NULL, stat_points INT NOT NULL, rank INT NOT NULL, UNIQUE INDEX UNIQ_937AB03470AA3482 (stats_id), INDEX IDX_937AB034B2A1D860 (species_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_attack (character_id INT NOT NULL, attack_id INT NOT NULL, INDEX IDX_E482EEA81136BE75 (character_id), INDEX IDX_E482EEA8F5315759 (attack_id), PRIMARY KEY(character_id, attack_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_guild (character_id INT NOT NULL, guild_id INT NOT NULL, INDEX IDX_8B839AC91136BE75 (character_id), INDEX IDX_8B839AC95F2131EF (guild_id), PRIMARY KEY(character_id, guild_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE combat_log (id INT AUTO_INCREMENT NOT NULL, logs LONGTEXT DEFAULT NULL, team_one LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', team_two LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', winner INT DEFAULT NULL, date_creation DATE NOT NULL, location VARCHAR(50) DEFAULT NULL, message VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE combat_log_character (combat_log_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_F4485C5045E363EF (combat_log_id), INDEX IDX_F4485C501136BE75 (character_id), PRIMARY KEY(combat_log_id, character_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dungeon (id INT AUTO_INCREMENT NOT NULL, area_id INT NOT NULL, name VARCHAR(30) NOT NULL, json VARCHAR(50) DEFAULT NULL, INDEX IDX_3FFA1F90BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guild (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mission_history (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, guild_id INT NOT NULL, ss_plus_rank_completed INT NOT NULL, ss_rank_completed INT NOT NULL, s_rank_completed INT NOT NULL, a_rank_completed INT NOT NULL, b_rank_completed INT NOT NULL, c_rank_completed INT NOT NULL, d_rank_completed INT NOT NULL, e_rank_completed INT NOT NULL, INDEX IDX_B686E4061136BE75 (character_id), INDEX IDX_B686E4065F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, types_id INT DEFAULT NULL, name VARCHAR(30) NOT NULL, is_playable TINYINT(1) NOT NULL, INDEX IDX_A50FF7128EB23357 (types_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stats (id INT AUTO_INCREMENT NOT NULL, vitality INT NOT NULL, strength INT NOT NULL, stamina INT NOT NULL, power INT NOT NULL, bravery INT NOT NULL, presence INT NOT NULL, impassiveness INT NOT NULL, agility INT NOT NULL, coordination INT NOT NULL, speed INT NOT NULL, action_point INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, attack_file VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, character_id INT DEFAULT NULL, discord_tag VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_8D93D6491136BE75 (character_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attack ADD CONSTRAINT FK_47C02D3BC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB03470AA3482 FOREIGN KEY (stats_id) REFERENCES stats (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE character_attack ADD CONSTRAINT FK_E482EEA81136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_attack ADD CONSTRAINT FK_E482EEA8F5315759 FOREIGN KEY (attack_id) REFERENCES attack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_guild ADD CONSTRAINT FK_8B839AC91136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_guild ADD CONSTRAINT FK_8B839AC95F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE combat_log_character ADD CONSTRAINT FK_F4485C5045E363EF FOREIGN KEY (combat_log_id) REFERENCES combat_log (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE combat_log_character ADD CONSTRAINT FK_F4485C501136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dungeon ADD CONSTRAINT FK_3FFA1F90BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE mission_history ADD CONSTRAINT FK_B686E4061136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE mission_history ADD CONSTRAINT FK_B686E4065F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF7128EB23357 FOREIGN KEY (types_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6491136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attack DROP FOREIGN KEY FK_47C02D3BC54C8C93');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB03470AA3482');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034B2A1D860');
        $this->addSql('ALTER TABLE character_attack DROP FOREIGN KEY FK_E482EEA81136BE75');
        $this->addSql('ALTER TABLE character_attack DROP FOREIGN KEY FK_E482EEA8F5315759');
        $this->addSql('ALTER TABLE character_guild DROP FOREIGN KEY FK_8B839AC91136BE75');
        $this->addSql('ALTER TABLE character_guild DROP FOREIGN KEY FK_8B839AC95F2131EF');
        $this->addSql('ALTER TABLE combat_log_character DROP FOREIGN KEY FK_F4485C5045E363EF');
        $this->addSql('ALTER TABLE combat_log_character DROP FOREIGN KEY FK_F4485C501136BE75');
        $this->addSql('ALTER TABLE dungeon DROP FOREIGN KEY FK_3FFA1F90BD0F409C');
        $this->addSql('ALTER TABLE mission_history DROP FOREIGN KEY FK_B686E4061136BE75');
        $this->addSql('ALTER TABLE mission_history DROP FOREIGN KEY FK_B686E4065F2131EF');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF7128EB23357');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6491136BE75');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE attack');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE character_attack');
        $this->addSql('DROP TABLE character_guild');
        $this->addSql('DROP TABLE combat_log');
        $this->addSql('DROP TABLE combat_log_character');
        $this->addSql('DROP TABLE dungeon');
        $this->addSql('DROP TABLE guild');
        $this->addSql('DROP TABLE mission_history');
        $this->addSql('DROP TABLE species');
        $this->addSql('DROP TABLE stats');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE `user`');
    }
}
