<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230816143956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_attack DROP FOREIGN KEY FK_E482EEA8F5315759');
        $this->addSql('ALTER TABLE character_attack DROP FOREIGN KEY FK_E482EEA81136BE75');
        $this->addSql('ALTER TABLE character_guild DROP FOREIGN KEY FK_8B839AC91136BE75');
        $this->addSql('ALTER TABLE character_guild DROP FOREIGN KEY FK_8B839AC95F2131EF');
        $this->addSql('ALTER TABLE mission_history DROP FOREIGN KEY FK_B686E4061136BE75');
        $this->addSql('ALTER TABLE mission_history DROP FOREIGN KEY FK_B686E4065F2131EF');
        $this->addSql('DROP TABLE character_attack');
        $this->addSql('DROP TABLE character_guild');
        $this->addSql('DROP TABLE guild');
        $this->addSql('DROP TABLE mission_history');
        $this->addSql('ALTER TABLE `character` DROP stat_points');
        $this->addSql('ALTER TABLE stats ADD level INT NOT NULL, ADD xp INT NOT NULL, ADD primary_stat_point INT NOT NULL, ADD secondary_stat_point INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_attack (character_id INT NOT NULL, attack_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E482EEA81136BE75 (character_id), INDEX IDX_E482EEA8F5315759 (attack_id), PRIMARY KEY(character_id, attack_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE character_guild (character_id INT NOT NULL, guild_id INT NOT NULL, INDEX IDX_8B839AC95F2131EF (guild_id), INDEX IDX_8B839AC91136BE75 (character_id), PRIMARY KEY(character_id, guild_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE guild (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mission_history (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, guild_id INT NOT NULL, ss_plus_rank_completed INT NOT NULL, ss_rank_completed INT NOT NULL, s_rank_completed INT NOT NULL, a_rank_completed INT NOT NULL, b_rank_completed INT NOT NULL, c_rank_completed INT NOT NULL, d_rank_completed INT NOT NULL, e_rank_completed INT NOT NULL, INDEX IDX_B686E4061136BE75 (character_id), INDEX IDX_B686E4065F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE character_attack ADD CONSTRAINT FK_E482EEA8F5315759 FOREIGN KEY (attack_id) REFERENCES attack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_attack ADD CONSTRAINT FK_E482EEA81136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_guild ADD CONSTRAINT FK_8B839AC91136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_guild ADD CONSTRAINT FK_8B839AC95F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_history ADD CONSTRAINT FK_B686E4061136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE mission_history ADD CONSTRAINT FK_B686E4065F2131EF FOREIGN KEY (guild_id) REFERENCES guild (id)');
        $this->addSql('ALTER TABLE `character` ADD stat_points INT NOT NULL');
        $this->addSql('ALTER TABLE stats DROP level, DROP xp, DROP primary_stat_point, DROP secondary_stat_point');
    }
}
