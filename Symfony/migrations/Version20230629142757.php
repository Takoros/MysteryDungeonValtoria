<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230629142757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE combat_log ADD dungeon_instance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE combat_log ADD CONSTRAINT FK_21AD4505C3DB61A1 FOREIGN KEY (dungeon_instance_id) REFERENCES dungeon_instance (id)');
        $this->addSql('CREATE INDEX IDX_21AD4505C3DB61A1 ON combat_log (dungeon_instance_id)');
        $this->addSql('ALTER TABLE dungeon_instance ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE combat_log DROP FOREIGN KEY FK_21AD4505C3DB61A1');
        $this->addSql('DROP INDEX IDX_21AD4505C3DB61A1 ON combat_log');
        $this->addSql('ALTER TABLE combat_log DROP dungeon_instance_id');
        $this->addSql('ALTER TABLE dungeon_instance DROP status');
    }
}
