<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Repository\ItemRepository;
use App\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Тесты контролера товаров
 */
class ItemControllerTest extends RestTestCase
{
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

    /**
     * Тестирование получения всех товаров
     */
    public function testGetItemsResponse()
    {
        $this->loadFixtures(
            [
                [
                    'id'    => 2,
                    'name'  => 'Товар 2',
                    'price' => 100.00,
                ],
                [
                    'id'    => 3,
                    'name'  => 'Товар 3',
                    'price' => 150.00,
                ],
            ],
            ItemRepository::TABLE_NAME
        );

        $response = $this->get('/api/get-items');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->isOk());

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(
            [
                [
                    'id'    => 2,
                    'name'  => 'Товар 2',
                    'price' => '100.00',
                ],
                [
                    'id'    => 3,
                    'name'  => 'Товар 3',
                    'price' => '150.00',
                ],
            ],
            $result
        );
    }

    /**
     * Проверка, что отдается пустой  респонс, когда в бд нет товаров
     */
    public function testGetItemsEmptyResponse()
    {
        $response = $this->get('/api/get-items');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->isOk());

        $result = json_decode($response->getContent(), true);
        $this->assertEquals([], $result);
    }
}
