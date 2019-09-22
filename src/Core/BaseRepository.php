<?php

declare(strict_types = 1);

namespace App\Core;

use PDO;

/**
 * Базовый класс репозитория сущности
 */
abstract class BaseRepository
{
    /**
     * Название таблицы сущности
     */
    const TABLE_NAME = '';

    /**
     * Первичный ключ по умолчанию
     */
    const PRIMARY_KEY = 'id';

    /**
     * Поля сущности в бд
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Класс сущности
     *
     * @var string
     */
    protected $entityClass;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * Конструктор
     *
     * @param string $entityClass Класс сущности
     */
    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;

        if (empty($this->fields)) {
            throw new \LogicException(
                sprintf('You must specify db fields for %s in %s', $entityClass, get_called_class())
            );
        }

        $this->checkEntityClassStructure();
    }

    /**
     * Установка PDO
     *
     * @param array $pdoConfig Конфиг для PDO
     */
    public function initPDO(array $pdoConfig)
    {
        $dsn      = $pdoConfig['dsn'];
        $user     = $pdoConfig['user'];
        $password = $pdoConfig['password'];

        $this->pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * Получить сущность по id
     *
     * @param int $id
     */
    public function findById(int $id)
    {
        // TODO
    }

    /**
     * Сохраняет изменения в БД
     *
     * @param EntityInterface $entity
     */
    public function save(EntityInterface $entity)
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field] = $entity->{'get'.ucfirst($field)}();
        }

        $id = $data[static::PRIMARY_KEY] ?? null;
        if (empty($id)) {
            unset($data[static::PRIMARY_KEY]);

            $id = $this->insertRow($data);
            // TODO сделать проставление св-ва id через ReflectionProperty
            $idSetter = 'set'.ucfirst(static::PRIMARY_KEY);
            $entity->{$idSetter}($id);
        } else {
            $this->updateRow((int)$id, $data);
        }
    }

    /**
     * Выполняет вставку записи в таблицу сущности
     *
     * @param array $data
     *
     * @return int
     */
    private function insertRow(array $data): int
    {
        $sql = sprintf(
            'INSERT INTO "%s" (%s) VALUES (%s)',
            static::TABLE_NAME,
            implode(', ', array_map(function ($field) {
                return $this->camelToSnakeCase($field);
            }, array_keys($data))),
            implode(', ', array_fill(0, count($data), '?'))
        );
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Обновляет запись в таблице сущности
     *
     * @param int   $id   Идентификатор записи
     * @param array $data Данные для обновления
     */
    private function updateRow(int $id, array $data)
    {
        $sql = sprintf(
            'UPDATE "%s" SET %s WHERE %s',
            static::TABLE_NAME,
            implode(' = ?,', array_map(function ($field) {
                return $this->camelToSnakeCase($field);
            }, array_keys($data))),
            static::PRIMARY_KEY . ' = ?'
        );

        $stmt = $this->pdo->prepare($sql);
        $params = array_values($data);
        $params[] = $id;

        $stmt->execute($params);
    }

    /**
     * Проверка правильности структуры сущности, с которой работает репозиторий
     *
     * @throws \LogicException
     */
    private function checkEntityClassStructure()
    {
        $entityClass = new \ReflectionClass($this->entityClass);
        foreach ($this->fields as $property) {
            if (!$entityClass->hasProperty($property)) {
                throw new \LogicException(
                    sprintf('Field "%s" should be specified in %s', $property, $this->entityClass)
                );
            }

            $getterMethod = 'get'.ucfirst($property);
            if (!$entityClass->hasMethod($getterMethod)) {
                throw new \LogicException(
                    sprintf('You should specify %s::%s', $this->entityClass, $getterMethod)
                );
            }
        }
    }

    /**
     * Преобразовывает название св-ва к snake case
     *
     * @param string $propertyName
     *
     * @return string
     */
    private function camelToSnakeCase(string $propertyName)
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($propertyName)));
    }
}
