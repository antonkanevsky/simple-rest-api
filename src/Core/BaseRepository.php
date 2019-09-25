<?php

declare(strict_types = 1);

namespace App\Core;

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
     * Маппинг полей к типам в бд
     */
    const FIELDS_TYPE_MAPPING = [];

    /**
     * Первичный ключ по умолчанию
     */
    const PRIMARY_KEY = 'id';

    // Типы колонок в БД
    const COLUMN_TYPE_INT       = 1;
    const COLUMN_TYPE_FLOAT     = 2;
    const COLUMN_TYPE_STRING    = 3;
    const COLUMN_TYPE_DATE_TIME = 4;

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
     * Соединение с БД
     *
     * @var DBConnection
     */
    protected $dbConnection;

    /**
     * Конструктор
     *
     * @param string $entityClass Класс сущности
     */
    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;

        $this->checkEntityClassStructure();
    }

    /**
     * Сетер для соединения с БД
     *
     * @param DBConnection $connection
     *
     * @return BaseRepository
     */
    public function setDBConnection(DBConnection $connection): self
    {
        $this->dbConnection = $connection;

        return $this;
    }

    /**
     * Получить сущность по id
     *
     * @param int|string $id
     *
     * @return EntityInterface|null
     */
    public function findById($id): ?EntityInterface
    {
        $sql = sprintf(
            'SELECT * FROM "%s" WHERE %s = ?',
            static::TABLE_NAME,
            static::PRIMARY_KEY
        );
        $stmt = $this->dbConnection->prepare($sql);

        $stmt->execute([(int) $id]);
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $this->makeEntity($result);
    }

    /**
     * Сохраняет изменения в БД
     *
     * @param EntityInterface $entity Объект модели
     */
    public function save(EntityInterface $entity)
    {
        $data = [];
        foreach ($this->fields as $field) {
            $property = $this->normalizeToCamelCase($field);
            $value    = $entity->{'get'.ucfirst($property)}();
            $data[$field] = $this->formatValueToDBFormat($field, $value);
        }

        $id = $data[static::PRIMARY_KEY] ?? null;
        if (empty($id)) {
            unset($data[static::PRIMARY_KEY]);

            $id = $this->insertRow($data);
            // TODO сделать проставление св-ва id через ReflectionProperty
            $idSetter = 'set'.ucfirst(static::PRIMARY_KEY);
            $entity->{$idSetter}((int)$id);
        } else {
            $this->updateRow((int)$id, $data);
        }
    }

    /**
     * Получить все объекты сущности
     *
     * @return array
     */
    public function findAll(): array
    {
        $sql  = sprintf('SELECT * FROM "%s"', static::TABLE_NAME);
        $stmt = $this->dbConnection->query($sql);

        $result = $stmt->fetchAll();

        return $this->makeEntityCollection($result);
    }

    /**
     * Выполняет вставку записи в таблицу сущности
     *
     * @param array $data
     *
     * @return string
     */
    private function insertRow(array $data)
    {
        $sql = sprintf(
            'INSERT INTO "%s" (%s) VALUES (%s)',
            static::TABLE_NAME,
            implode(', ', array_map(function ($field) {
                return $this->camelToSnakeCase($field);
            }, array_keys($data))),
            implode(', ', array_fill(0, count($data), '?'))
        );
        $stmt = $this->dbConnection->prepare($sql);

        $stmt->execute(array_values($data));

        return $this->dbConnection->getLastInsertId();
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

        $stmt = $this->dbConnection->prepare($sql);
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
        if (empty($this->fields)) {
            throw new \LogicException(
                sprintf('You must specify db fields for %s in %s', $this->entityClass, get_called_class())
            );
        }

        $entityClass = new \ReflectionClass($this->entityClass);
        foreach ($this->fields as $property) {
            $property = $this->normalizeToCamelCase($property);
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

            $setterMethod = 'set'.ucfirst($property);
            if (!$entityClass->hasMethod($setterMethod)) {
                throw new \LogicException(
                    sprintf('You should specify %s::%s', $this->entityClass, $setterMethod)
                );
            }
        }
    }

    /**
     * Получить коллекцию сущностей
     *
     * @param array $entitiesDataArray Результат выборки из бд
     *
     * @return  EntityInterface[]
     */
    private function makeEntityCollection(array $entitiesDataArray): array
    {
        $collection  = [];
        foreach ($entitiesDataArray as $data) {
            $collection[] = $this->makeEntity($data);
        }

        return $collection;
    }

    /**
     * Получить сущность из данных записи в БД
     *
     * @param array $data
     *
     * @return EntityInterface
     */
    private function makeEntity(array $data): EntityInterface
    {
        $entityClass = $this->entityClass;
        $entity = new $entityClass();

        foreach ($data as $key => $value) {
            $value    = $this->formatValueToEntityFormat($key, $value);
            $property = $this->normalizeToCamelCase($key);
            $entity->{'set'.ucfirst($property)}($value);
        }

        return $entity;
    }

    /**
     * Форматирует значение столбца из БД в формат хранимый в сущности
     *
     * @param string $columnName
     * @param mixed  $value
     *
     * @return mixed
     */
    private function formatValueToEntityFormat(string $columnName, $value)
    {
        if (!isset(static::FIELDS_TYPE_MAPPING[$columnName])) {
            return $value;
        }

        $fieldType = static::FIELDS_TYPE_MAPPING[$columnName];
        switch ($fieldType) {
            case self::COLUMN_TYPE_INT:
                $value = (int) $value;
                break;
            case self::COLUMN_TYPE_STRING:
                $value = (string) $value;
                break;
            case self::COLUMN_TYPE_FLOAT:
                $value = (float) $value;
                break;
            case self::COLUMN_TYPE_DATE_TIME:
                $value = new \DateTimeImmutable($value);
                break;
            default:
                $value = (string) $value;
        }

        return $value;
    }

    /**
     * Форматирует значение св-ва сущности в формат для БД
     *
     * @param string $columnName
     * @param mixed  $value
     *
     * @return mixed
     */
    private function formatValueToDBFormat(string $columnName, $value)
    {
        if (!isset(static::FIELDS_TYPE_MAPPING[$columnName]) || $value === null) {
            return $value;
        }

        $fieldType = static::FIELDS_TYPE_MAPPING[$columnName];
        switch ($fieldType) {
            case self::COLUMN_TYPE_DATE_TIME:
                $value = $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value;
                break;
            case self::COLUMN_TYPE_FLOAT:
                $value = (float) $value;
                break;
            case self::COLUMN_TYPE_INT:
                $value = (int) $value;
                break;
            default:
                $value = (string) $value;
        }

        return $value;
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

    /**
     * Преобразовывает название колонки к camelCase
     *
     * @param string $column
     *
     * @return string
     */
    private function normalizeToCamelCase(string $column): string
    {
        $camelCasedName = preg_replace_callback('/(^|_|\.)+(.)/', function ($match) {
            return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
        }, $column);

        return lcfirst($camelCasedName);
    }
}
