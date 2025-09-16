<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916145521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE entry ADD flags JSON NOT NULL");
        $this->addSql("UPDATE entry SET flags = '[]' WHERE kind = 'default'");
        $this->addSql("UPDATE entry SET flags = '[\"periodic_entry\"]' WHERE kind = 'balancing'");
        $this->addSql("UPDATE entry SET flags = '[\"transfert\"]' WHERE name LIKE 'Transfer%' AND kind = 'balancing'");
        $this->addSql("UPDATE entry SET flags = '[\"balance\"]' WHERE name LIKE 'Équilibrage%' AND kind = 'balancing'");
        $this->addSql("UPDATE entry SET flags = '[\"hidden\"]' WHERE (name LIKE 'Initial%' OR name LIKE 'Import%') AND kind = 'balancing'");

        $this->addSql('ALTER TABLE entry DROP kind');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry ADD kind VARCHAR(255) DEFAULT \'default\' NOT NULL, DROP flags');
    }
}
