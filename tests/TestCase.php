<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Core\Application;
use App\Core\DBConnection;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * Подключение к БД
     *
     * @var DBConnection
     */
    private $dbConnection;

    /**
     * Установка окружения
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application  = new Application('test');
        $this->dbConnection = $this->getContainer()->get('app.db_connection');
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
            $this->dbConnection
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
