<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218151635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_package (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, package_id INT NOT NULL, INDEX IDX_2812CEDE8D9F6D38 (order_id), INDEX IDX_2812CEDEF44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_package_item (order_package_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_A096D4BC479656AA (order_package_id), INDEX IDX_A096D4BC4584665A (product_id), PRIMARY KEY(order_package_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_package ADD CONSTRAINT FK_2812CEDE8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_package ADD CONSTRAINT FK_2812CEDEF44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE order_package_item ADD CONSTRAINT FK_A096D4BC479656AA FOREIGN KEY (order_package_id) REFERENCES order_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_package_item ADD CONSTRAINT FK_A096D4BC4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `order` ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398A76ED395 ON `order` (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_package DROP FOREIGN KEY FK_2812CEDE8D9F6D38');
        $this->addSql('ALTER TABLE order_package DROP FOREIGN KEY FK_2812CEDEF44CABFF');
        $this->addSql('ALTER TABLE order_package_item DROP FOREIGN KEY FK_A096D4BC479656AA');
        $this->addSql('ALTER TABLE order_package_item DROP FOREIGN KEY FK_A096D4BC4584665A');
        $this->addSql('DROP TABLE order_package');
        $this->addSql('DROP TABLE order_package_item');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP INDEX UNIQ_F5299398A76ED395 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP user_id');
    }
}
