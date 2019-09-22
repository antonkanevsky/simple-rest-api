<?php

declare(strict_types = 1);

namespace App\Tests;

use PDO;

/**
 * Трейт для обновления схемы тестовой БД
 */
trait SchemaTrait
{
    /**
     * Подключение к тестовой БД
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Обновить схему в тестовой БД
     */
    protected function updateSchema(): void
    {
        $sql = <<<'SQL'
CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    status VARCHAR(32) NOT NULL
);
CREATE TABLE items (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DOUBLE PRECISION NOT NULL
);
SQL;

        $this->pdo->exec($sql);
    }

    /**
     * Откатить схему в тестовой БД
     */
    protected function dropSchema(): void
    {
        $sql = <<<'SQL'
DROP TABLE orders;
DROP TABLE items;
SQL;
        $this->pdo->exec($sql);
    }
}