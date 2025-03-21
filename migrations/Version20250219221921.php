<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219221921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders ADD contact_id INT NOT NULL, ADD address_id INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEEE7A1254A ON orders (contact_id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEEF5B7AF75 ON orders (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEE7A1254A');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEF5B7AF75');
        $this->addSql('DROP INDEX IDX_E52FFDEEE7A1254A ON orders');
        $this->addSql('DROP INDEX IDX_E52FFDEEF5B7AF75 ON orders');
        $this->addSql('ALTER TABLE orders DROP contact_id, DROP address_id');
    }
}
