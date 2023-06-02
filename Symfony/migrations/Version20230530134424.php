<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530134424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE combat_log CHANGE logs logs LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE team_one team_one LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE team_two team_two LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE combat_log CHANGE logs logs LONGTEXT DEFAULT NULL, CHANGE team_one team_one LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE team_two team_two LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }
}
