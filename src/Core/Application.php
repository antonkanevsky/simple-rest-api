<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader as DIYamlFileLoader;

/**
 * Базовый класс приложения
 */
class Application
{
    /**
     * DI контейнер
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var UrlMatcher
     */
    private $matcher;

    /**
     * Корневая директория проекта
     *
     * @var string
     */
    private $rootDir;

    /**
     * Признак окружения
     *
     * @var string
     */
    private $environment;

    /**
     * Конструктор
     *
     * @string $environment Признак окружения приложения
     */
    public function __construct(string $environment = 'prod')
    {
        $this->rootDir     = realpath(dirname(__DIR__, 2));
        $this->environment = $environment;

        $this->initDIContainer();
    }

    /**
     * Обработка запроса к приложению
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $this->initURLMatcher($request);

        try {
            $match = $this->matcher->matchRequest($request);

            list($controllerClass, $method) = explode('::', $match['_controller'], 2);

            return $this->getResponse($controllerClass, $method, $request);
        } catch (RoutingException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());
            throw new \RuntimeException($message, Response::HTTP_NOT_FOUND, $e);
        } catch (\Exception $e) {
            $message = sprintf(
                'Error while handling request "%s %s"',
                $request->getMethod(),
                $request->getPathInfo()
            );
            throw new \RuntimeException($message, Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
    }

    /**
     * Получить контейнер зависимостей
     *
     * @return ContainerBuilder
     */
    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }

    /**
     * Инициализирует DI контейнер
     */
    private function initDIContainer(): void
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add([
            'app.root_dir'    => $this->rootDir,
            'app.environment' => $this->environment,
        ]);

        $confDir = $this->rootDir.'/config';
        $loader  = new DIYamlFileLoader($container, new FileLocator($confDir));
        $loader->load('services.yaml');
        // Настройка сервисов для конкретного окружения
        $environmentConfigFile = sprintf($confDir.'/services_%s.yaml', $this->environment);
        if (file_exists($environmentConfigFile)) {
            $loader->load($environmentConfigFile);
        }

        $container->compile();

        $this->container = $container;
    }

    /**
     * Инициализирует сопоставитель роутов
     *
     * @param Request $request
     */
    private function initURLMatcher(Request $request): void
    {
        $loader = new YamlFileLoader(new FileLocator($this->rootDir.'/config'));
        $routes = $loader->load('routes.yaml');

        $context = new RequestContext();
        $context->fromRequest($request);

        $this->matcher = new UrlMatcher($routes, $context);
    }

    /**
     * Получает респонс из контролера
     *
     * @param string  $controllerClass
     * @param string  $method
     * @param Request $request
     *
     * @return Response
     */
    private function getResponse(string $controllerClass, string $method, Request $request): Response
    {
        if (!$this->container->has($controllerClass)) {
            throw new \RuntimeException(\sprintf('%s is not found in DI container', $controllerClass));
        }

        $controller = $this->container->get($controllerClass);

        // Установка реквеста тем контролерам, которым он нужен
        if ($controller instanceof RequestAwareInterface) {
            $controller->setRequest($request);
        }

        $response = $controller->{$method}();

        if (!$response instanceof Response) {
            throw new \LogicException(
                sprintf(
                    'Return value of %s::%s should be the the instance of "%s"',
                    $controllerClass,
                    $method,
                    'Symfony\Component\HttpFoundation\Response'
                )
            );
        }

        return $response;
    }
}
