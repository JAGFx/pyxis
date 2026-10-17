<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017160107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link entries to their assignment and add an optional assignment target';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assignment ADD target DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE entry ADD assignment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entry ADD CONSTRAINT FK_2B219D70D19302F8 FOREIGN KEY (assignment_id) REFERENCES assignment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2B219D70D19302F8 ON entry (assignment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assignment DROP target');
        $this->addSql('ALTER TABLE entry DROP FOREIGN KEY FK_2B219D70D19302F8');
        $this->addSql('DROP INDEX IDX_2B219D70D19302F8 ON entry');
        $this->addSql('ALTER TABLE entry DROP assignment_id');
    }
}
