<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220134749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_package DROP FOREIGN KEY FK_2812CEDEF44CABFF');
        $this->addSql('DROP INDEX IDX_2812CEDEF44CABFF ON order_package');
        $this->addSql('ALTER TABLE order_package ADD package_name VARCHAR(50) NOT NULL, DROP package_id');
        $this->addSql('ALTER TABLE order_package_item DROP FOREIGN KEY FK_A096D4BC4584665A');
        $this->addSql('DROP INDEX IDX_A096D4BC4584665A ON order_package_item');
        $this->addSql('DROP INDEX `primary` ON order_package_item');
        $this->addSql('ALTER TABLE order_package_item ADD product_name VARCHAR(50) NOT NULL, DROP product_id');
        $this->addSql('ALTER TABLE order_package_item ADD PRIMARY KEY (order_package_id, product_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_package ADD package_id INT NOT NULL, DROP package_name');
        $this->addSql('ALTER TABLE order_package ADD CONSTRAINT FK_2812CEDEF44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('CREATE INDEX IDX_2812CEDEF44CABFF ON order_package (package_id)');
        $this->addSql('DROP INDEX `PRIMARY` ON order_package_item');
        $this->addSql('ALTER TABLE order_package_item ADD product_id INT NOT NULL, DROP product_name');
        $this->addSql('ALTER TABLE order_package_item ADD CONSTRAINT FK_A096D4BC4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_A096D4BC4584665A ON order_package_item (product_id)');
        $this->addSql('ALTER TABLE order_package_item ADD PRIMARY KEY (order_package_id, product_id)');
    }
}
