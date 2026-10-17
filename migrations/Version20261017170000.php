<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017170000 extends AbstractMigration
{
    /**
     * Money columns: table => [column => nullable].
     */
    private const array COLUMNS = [
        'entry'          => ['amount' => false],
        'budget'         => ['amount' => false],
        'periodic_entry' => ['amount' => true],
        'assignment'     => ['amount' => false, 'target' => true],
        'history_budget' => ['amount' => false, 'spent' => true],
    ];

    public function getDescription(): string
    {
        return 'Store money amounts as integer cents instead of floating point euros';
    }

    /**
     * MySQL commits DDL implicitly: the data conversion and the ALTER cannot share a transaction.
     */
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $nullable) {
                // ROUND first: 8.33 is stored as 8.3299999..., truncating would give 832
                $this->addSql(sprintf('UPDATE %1$s SET %2$s = ROUND(%2$s * 100)', $table, $column));
                $this->addSql(sprintf('ALTER TABLE %1$s CHANGE %2$s %2$s INT %3$s', $table, $column, $nullable ? 'DEFAULT NULL' : 'NOT NULL'));
            }
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $nullable) {
                $this->addSql(sprintf('ALTER TABLE %1$s CHANGE %2$s %2$s DOUBLE PRECISION %3$s', $table, $column, $nullable ? 'DEFAULT NULL' : 'NOT NULL'));
                $this->addSql(sprintf('UPDATE %1$s SET %2$s = %2$s / 100', $table, $column));
            }
        }
    }
}
