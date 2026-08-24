<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260823000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create one loyalty account per user';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof SQLitePlatform) {
            return;
        }

        $this->addSql('CREATE TABLE loyalty_account (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, points INTEGER DEFAULT 0 NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_11F7BE17A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11F7BE17A76ED395 ON loyalty_account (user_id)');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof SQLitePlatform) {
            return;
        }

        $this->addSql('DROP TABLE loyalty_account');
    }
}
