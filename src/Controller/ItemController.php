<?php

declare(strict_types = 1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контролер товаров
 */
class ItemController
{
    public function createItems(): JsonResponse
    {
        $request = Request::createFromGlobals();

        return new JsonResponse(
            [
                'hello' => 'World'
            ]
        );
    }
}
