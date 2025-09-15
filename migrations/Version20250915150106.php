<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915150106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ADD enabled TINYINT(1) DEFAULT 1 NOT NULL, DROP enable');
        $this->addSql('ALTER TABLE budget ADD enabled TINYINT(1) DEFAULT 1 NOT NULL, DROP enable');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ADD enable TINYINT(1) DEFAULT 0 NOT NULL, DROP enabled');
        $this->addSql('ALTER TABLE budget ADD enable TINYINT(1) NOT NULL, DROP enabled');
    }
}
