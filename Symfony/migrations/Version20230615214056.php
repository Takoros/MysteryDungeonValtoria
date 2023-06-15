<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615214056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rotation (id INT AUTO_INCREMENT NOT NULL, attack_one_id VARCHAR(50) NOT NULL, attack_two_id VARCHAR(50) NOT NULL, attack_three_id VARCHAR(50) NOT NULL, attack_four_id VARCHAR(50) NOT NULL, attack_five_id VARCHAR(50) NOT NULL, character_id INT NOT NULL, type VARCHAR(50) NOT NULL, INDEX IDX_297C98F1FC29468F (attack_one_id), INDEX IDX_297C98F19775A140 (attack_two_id), INDEX IDX_297C98F14936C4D2 (attack_three_id), INDEX IDX_297C98F11C126C43 (attack_four_id), INDEX IDX_297C98F140259AA8 (attack_five_id), INDEX IDX_297C98F11136BE75 (character_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F1FC29468F FOREIGN KEY (attack_one_id) REFERENCES attack (id)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F19775A140 FOREIGN KEY (attack_two_id) REFERENCES attack (id)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F14936C4D2 FOREIGN KEY (attack_three_id) REFERENCES attack (id)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F11C126C43 FOREIGN KEY (attack_four_id) REFERENCES attack (id)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F140259AA8 FOREIGN KEY (attack_five_id) REFERENCES attack (id)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F11136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F1FC29468F');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F19775A140');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F14936C4D2');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F11C126C43');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F140259AA8');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F11136BE75');
        $this->addSql('DROP TABLE rotation');
    }
}
