<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * DI контейнер
 */
class DIContainer
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var YamlFileLoader
     */
    private $loader;

    /**
     * Конструктор
     *
     * @param string Имя файла с конфигурацией сервисов
     */
    public function __construct(string $configFilename)
    {
        $this->container = new ContainerBuilder();
        $this->loader    = new YamlFileLoader($this->container, new FileLocator(dirname(__DIR__, 2)));

        $this->loader->load($configFilename);
        $this->container->compile();
    }

    /**
     * Получить сервис по названию
     *
     * @param string $serviceName Имя сервиса
     *
     * @return object
     */
    public function get(string $serviceName)
    {
        return $this->container->get($serviceName);
    }

    /**
     * Проверяет, есть ли сервис в DI-контейнере
     *
     * @param string $serviceName Имя сервиса
     *
     * @return bool
     */
    public function has(string $serviceName)
    {
        return $this->container->has($serviceName);
    }
}
