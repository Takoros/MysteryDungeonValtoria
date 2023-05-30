<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530091525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_type (character_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_9B2243B11136BE75 (character_id), INDEX IDX_9B2243B1C54C8C93 (type_id), PRIMARY KEY(character_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_type ADD CONSTRAINT FK_9B2243B11136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_type ADD CONSTRAINT FK_9B2243B1C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_type DROP FOREIGN KEY FK_9B2243B11136BE75');
        $this->addSql('ALTER TABLE character_type DROP FOREIGN KEY FK_9B2243B1C54C8C93');
        $this->addSql('DROP TABLE character_type');
    }
}
