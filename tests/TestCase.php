<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Core\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PDO;

/**
 * Базовый класс для функциональных тестов
 */
abstract class TestCase extends BaseTestCase
{
    use SchemaTrait;
    use FixturesTrait;

    /**
     * Инстанс REST API приложения
     *
     * @var Application
     */
    protected $application;

    /**
     * Подключение к тестовой БД
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Установка окружения
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application('test');

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $dsn = $this->getContainer()->getParameter('pdo')['dsn'];
        $this->pdo = new PDO($dsn, null, null, $options);
        $this->updateSchema();
    }

    /**
     * Сброс окружения
     */
    protected function tearDown()
    {
        $this->dropSchema();
        unset(
            $this->application,
            $this->pdo
        );
    }

    /**
     * Получить DI контейнер
     *
     * @return ContainerBuilder
     */
    protected function getContainer(): ContainerBuilder
    {
        return $this->application->getContainer();
    }
}
