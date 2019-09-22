<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Tests\RestTestCase;
use http\Env\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Тесты контролера товаров
 */
class ItemControllerTest extends RestTestCase
{
    /**
     * Поднимает окружение
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Проверка корректного респонса при генерации товаров
     */
    public function testCreateItemsResponse()
    {
        $response = $this->post('/api/create-items');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->isOk());

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(
            [
                [
                    'id'    => 1,
                    'name'  => 'Товар 1',
                    'price' => '100.00',
                ],
                [
                    'id'    => 2,
                    'name'  => 'Товар 2',
                    'price' => '150.00',
                ],
            ],
            $result
        );
    }
}
