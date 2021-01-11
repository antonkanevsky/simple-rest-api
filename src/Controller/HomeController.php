<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Контролер для домашней страницы API приложения
 */
class HomeController
{
    /**
     * Приветствие для пользователей API
     */
    public function greetAPIUsers(): Response
    {
        return new Response(
            '<h2>Приветствуем на главной странице пользователей REST API нашего приложения</h2>',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }
}
