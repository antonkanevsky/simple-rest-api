<?php

declare(strict_types = 1);

namespace App\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Базовый класс для теста REST API.
 */
abstract class RestTestCase extends TestCase
{
    /**
     * Заголовки запроса
     *
     * @var array
     */
    protected $headers;

    /**
     * Поднимает окружение
     */
    protected function setUp()
    {
        parent::setUp();

        $this->headers = ['Content-Type' => 'application/json'];
    }

    /**
     * Выполняет GET запрос
     *
     * @param string $uri
     * @param array  $parameters
     * @param array  $headers
     *
     * @return Response
     */
    protected function get(string $uri, array $parameters = [], array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);

        return $this->doRequest($uri, 'GET', $parameters, [], $headers);
    }

    /**
     * Выполняет POST запрос
     *
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $headers
     * @param string $content
     *
     * @return Response
     */
    protected function post(
        string $uri,
        array $parameters = [],
        array $files = [],
        array $headers = [],
        string $content = null
    ) {
        $headers = array_merge($this->headers, $headers);

        return $this->doRequest($uri, 'POST', $parameters, $files, $headers, $content);
    }

    /**
     * Выполняет запрос к приложению
     *
     * @param string $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $files
     * @param array  $headers
     * @param string $content
     *
     * @return Response
     */
    private function doRequest(
        string $uri,
        string $method,
        array $parameters,
        array $files = [],
        array $headers = [],
        string $content = null
    ) {
        $request = Request::create($uri, $method, $parameters, [], $files, $headers, $content);

        $response = $this->application->handle($request);

        return $response;
    }
}
