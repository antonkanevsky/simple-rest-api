<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * Базовый класс приложения
 */
class Application
{
    /**
     * @var DIContainer
     */
    private $container;

    /**
     * @var UrlMatcher
     */
    private $matcher;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->container = new DIContainer('config/services.yaml');
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

            return $this->getResponse($controllerClass, $method);
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
     * Инициализирует сопоставитель роутов
     *
     * @param Request $request
     */
    private function initURLMatcher(Request $request): void
    {
        $loader = new YamlFileLoader(new FileLocator(dirname(__DIR__, 2).'/config'));
        $routes = $loader->load('routes.yaml');

        $context = new RequestContext();
        $context->fromRequest($request);

        $this->matcher = new UrlMatcher($routes, $context);
    }

    /**
     * Получает респонс из контролера
     *
     * @param string $controllerClass
     * @param string $method
     *
     * @return Response
     */
    private function getResponse(string $controllerClass, string $method): Response
    {
        if (!$this->container->has($controllerClass)) {
            throw new \RuntimeException(\sprintf('%s is not found in DI container', $controllerClass));
        }

        $controller = $this->container->get($controllerClass);

        $response = call_user_func([$controller, $method]);

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
