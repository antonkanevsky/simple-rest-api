<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $matcher = $this->initURLMatcher($request);
        $match = $matcher->match($request->getPathInfo());

        if ($match) {
            return new Response('Route found!!!');
        }

        return new Response('No route found');
    }

    private function initURLMatcher(Request $request)
    {
        $loader = new YamlFileLoader(new FileLocator(dirname(__DIR__, 2).'/config'));
        $routes = $loader->load('routes.yaml');

        $context = new RequestContext();
        $context->fromRequest($request);

        return new UrlMatcher($routes, $context);
    }
}
