<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow to disable periodic entries, automatically once their assignment target is reached';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE periodic_entry ADD enabled TINYINT(1) DEFAULT 1 NOT NULL, ADD disable_when_target_reached TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE periodic_entry DROP enabled, DROP disable_when_target_reached');
    }
}
