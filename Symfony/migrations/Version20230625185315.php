<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625185315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attack ADD attack_tree_id INT NOT NULL');
        $this->addSql('ALTER TABLE attack ADD CONSTRAINT FK_47C02D3BFE350845 FOREIGN KEY (attack_tree_id) REFERENCES type (id)');
        $this->addSql('CREATE INDEX IDX_47C02D3BFE350845 ON attack (attack_tree_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attack DROP FOREIGN KEY FK_47C02D3BFE350845');
        $this->addSql('DROP INDEX IDX_47C02D3BFE350845 ON attack');
        $this->addSql('ALTER TABLE attack DROP attack_tree_id');
    }
}
