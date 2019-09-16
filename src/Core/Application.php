<?php

declare(strict_types = 1);

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function handle(Request $request)
    {
        return new Response('Okay');
    }
}
