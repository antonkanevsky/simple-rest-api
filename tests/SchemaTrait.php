<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Core\DBConnection;

/**
 * Трейт для обновления схемы тестовой БД
 */
trait SchemaTrait
{
    /**
     * Подключение к БД
     *
     * @var DBConnection
     */
    private $dbConnection;

    /**
     * Обновить схему в тестовой БД
     */
    protected function updateSchema(): void
    {
        $sql = <<<'SQL'
CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    status VARCHAR(32) NOT NULL,
    created_at datetime default current_timestamp,
    amount DOUBLE PRECISION NOT NULL
);
CREATE TABLE items (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DOUBLE PRECISION NOT NULL
);
CREATE TABLE order_item (
    order_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    FOREIGN KEY(order_id) REFERENCES orders(id),
    FOREIGN KEY(item_id) REFERENCES items(id),
    PRIMARY KEY (order_id, item_id)
);
SQL;

        $this->dbConnection->exec($sql);
    }

    /**
     * Откатить схему в тестовой БД
     */
    protected function dropSchema(): void
    {
        $sql = <<<'SQL'
DROP TABLE orders;
DROP TABLE items;
DROP TABLE order_item;
SQL;
        $this->dbConnection->exec($sql);
    }
}
