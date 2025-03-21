<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219221120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4C62E638');
        $this->addSql('DROP TABLE contact');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_package ADD CONSTRAINT FK_2812CEDE8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEED4E6F81');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE8D93D649');
        $this->addSql('DROP INDEX IDX_E52FFDEED4E6F81 ON orders');
        $this->addSql('DROP INDEX IDX_E52FFDEE4C62E638 ON orders');
        $this->addSql('DROP INDEX IDX_E52FFDEE8D93D649 ON orders');
        $this->addSql('ALTER TABLE orders DROP user, DROP address, DROP contact');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C17248D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone_number BIGINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE orders ADD user INT DEFAULT NULL, ADD address INT DEFAULT NULL, ADD contact INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEED4E6F81 FOREIGN KEY (address) REFERENCES address (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4C62E638 FOREIGN KEY (contact) REFERENCES contact (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE8D93D649 FOREIGN KEY (user) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E52FFDEED4E6F81 ON orders (address)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE4C62E638 ON orders (contact)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE8D93D649 ON orders (user)');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_package DROP FOREIGN KEY FK_2812CEDE8D9F6D38');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C17248D9F6D38');
    }
}
