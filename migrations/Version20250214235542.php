<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214235542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_package (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, package_id INT NOT NULL, INDEX IDX_25B2AF921AD5CDBF (cart_id), INDEX IDX_25B2AF92F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_package_item (cart_package_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_D2D9D2BF9426DB0D (cart_package_id), INDEX IDX_D2D9D2BF4584665A (product_id), PRIMARY KEY(cart_package_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_package ADD CONSTRAINT FK_25B2AF921AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE cart_package ADD CONSTRAINT FK_25B2AF92F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE cart_package_item ADD CONSTRAINT FK_D2D9D2BF9426DB0D FOREIGN KEY (cart_package_id) REFERENCES cart_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_package_item ADD CONSTRAINT FK_D2D9D2BF4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        $this->addSql('DROP TABLE cart_item');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_item (cart_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_F0FE25271AD5CDBF (cart_id), INDEX IDX_F0FE25274584665A (product_id), PRIMARY KEY(cart_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_package DROP FOREIGN KEY FK_25B2AF921AD5CDBF');
        $this->addSql('ALTER TABLE cart_package DROP FOREIGN KEY FK_25B2AF92F44CABFF');
        $this->addSql('ALTER TABLE cart_package_item DROP FOREIGN KEY FK_D2D9D2BF9426DB0D');
        $this->addSql('ALTER TABLE cart_package_item DROP FOREIGN KEY FK_D2D9D2BF4584665A');
        $this->addSql('DROP TABLE cart_package');
        $this->addSql('DROP TABLE cart_package_item');
    }
}
