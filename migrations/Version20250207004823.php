<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207004823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD address INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D4E6F81 FOREIGN KEY (address) REFERENCES address (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8D93D649D4E6F81 ON user (address)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649D4E6F81');
        $this->addSql('DROP INDEX IDX_8D93D649D4E6F81 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP address');
    }
}
