<?php

declare(strict_types = 1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Слой соединения с БД
 */
class DBConnection
{
    /**
     * Объект соединения с БД
     *
     * @var \PDO
     */
    private $connection;

    /**
     * Конструктор
     *
     * @param array $params Конфиг для соединения
     */
    public function __construct(array $params)
    {
        $dsn      = $params['dsn'];
        $user     = $params['user'];
        $password = $params['password'];

        $this->connection = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * Начать транзакцию
     *
     * @return DBConnection
     */
    public function beginTransaction(): self
    {
        $this->connection->beginTransaction();
    }

    /**
     * Коммит транзакции
     *
     * @return DBConnection
     */
    public function commit(): self
    {
        $this->connection->commit();
    }

    /**
     * Откат транзакции
     *
     * @return DBConnection
     */
    public function rollback(): self
    {
        $this->connection->rollBack();
    }

    /**
     * Возвращает id последней добавленной записи
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Подготавливает SQL-запрос к выполнению
     *
     * @param string $sql
     * @param array  $driverOptions
     *
     * @return PDOStatement
     */
    public function prepare(string $sql, array $driverOptions = []): PDOStatement
    {
        return $this->connection->prepare($sql, $driverOptions);
    }

    /**
     * Выполняет SQL-запрос и возвращает подготовленный запрос
     *
     * @param string $sql
     *
     * @return PDOStatement
     */
    public function query(string $sql): PDOStatement
    {
        return $this->connection->query($sql);
    }

    /**
     * Выполняет SQL-запрос
     *
     * @param string $sql
     *
     * @return int
     */
    public function exec(string $sql): int
    {
        return $this->connection->exec($sql);
    }
}
