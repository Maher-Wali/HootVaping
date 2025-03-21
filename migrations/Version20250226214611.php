<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226214611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, discount INT NOT NULL, max_uses INT NOT NULL, current_uses INT NOT NULL, is_active TINYINT(1) NOT NULL, expiration_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3D8C939E77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promo_code_user (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, promo_code_id INT NOT NULL, INDEX IDX_93B2DB33A76ED395 (user_id), INDEX IDX_93B2DB332FAE4625 (promo_code_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promo_code_user ADD CONSTRAINT FK_93B2DB33A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE promo_code_user ADD CONSTRAINT FK_93B2DB332FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_code (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promo_code_user DROP FOREIGN KEY FK_93B2DB33A76ED395');
        $this->addSql('ALTER TABLE promo_code_user DROP FOREIGN KEY FK_93B2DB332FAE4625');
        $this->addSql('DROP TABLE promo_code');
        $this->addSql('DROP TABLE promo_code_user');
    }
}
