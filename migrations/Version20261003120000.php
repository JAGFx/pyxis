<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional assignment to periodic entry';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE periodic_entry ADD assignment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE periodic_entry ADD CONSTRAINT FK_8FA2A6EBD19302F8 FOREIGN KEY (assignment_id) REFERENCES assignment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8FA2A6EBD19302F8 ON periodic_entry (assignment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE periodic_entry DROP FOREIGN KEY FK_8FA2A6EBD19302F8');
        $this->addSql('DROP INDEX IDX_8FA2A6EBD19302F8 ON periodic_entry');
        $this->addSql('ALTER TABLE periodic_entry DROP assignment_id');
    }
}
