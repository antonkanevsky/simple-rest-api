<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Core\DBConnection;

/**
 * Трейт для загрузки фикстур
 */
trait FixturesTrait
{
    /**
     * Подключение к БД
     *
     * @var DBConnection
     */
    private $dbConnection;

    /**
     * Загружает данные в тестовую БД
     *
     * @param array  $dataRows Данные для загрузки
     * @param string $table    Название таблицы
     */
    public function loadFixtures(array $dataRows, string $table)
    {
        foreach ($dataRows as $data) {
            $sql = sprintf(
                'INSERT INTO "%s" (%s) VALUES (%s)',
                $table,
                implode(', ', array_keys($data)),
                implode(', ', array_fill(0, count($data), '?'))
            );
            $stmt = $this->dbConnection->prepare($sql);

            $stmt->execute(array_values($data));
        }
    }
}
