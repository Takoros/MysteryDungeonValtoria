<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628124619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE timers (id INT AUTO_INCREMENT NOT NULL, last_dungeon DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `character` ADD timers_id INT NOT NULL');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB03455222ED4 FOREIGN KEY (timers_id) REFERENCES timers (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_937AB03455222ED4 ON `character` (timers_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB03455222ED4');
        $this->addSql('DROP TABLE timers');
        $this->addSql('DROP INDEX UNIQ_937AB03455222ED4 ON `character`');
        $this->addSql('ALTER TABLE `character` DROP timers_id');
    }
}
