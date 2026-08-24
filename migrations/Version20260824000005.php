<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert the initial products when the catalog is empty';
    }

    public function up(Schema $schema): void
    {
        if ((int) $this->connection->fetchOne('SELECT COUNT(*) FROM product') > 0) {
            return;
        }

        $isPostgreSql = $this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform;
        $available = $isPostgreSql ? 'TRUE' : '1';
        $unavailable = $isPostgreSql ? 'FALSE' : '0';

        $this->addSql("INSERT INTO product (name, image, description, price, rating, available) VALUES ('Sandwich jambon fromage', 'https://placehold.co/300x200', 'Sandwich au jambon et au fromage.', 4.5, 4.2, {$available})");
        $this->addSql("INSERT INTO product (name, image, description, price, rating, available) VALUES ('Salade César', 'https://placehold.co/300x200', 'Salade César prête à consommer.', 6.9, 4.5, {$available})");
        $this->addSql("INSERT INTO product (name, image, description, price, rating, available) VALUES ('Cookie chocolat', 'https://placehold.co/300x200', 'Cookie aux pépites de chocolat.', 2.2, 4.7, {$unavailable})");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM product WHERE name IN ('Sandwich jambon fromage', 'Salade César', 'Cookie chocolat')");
    }
}
