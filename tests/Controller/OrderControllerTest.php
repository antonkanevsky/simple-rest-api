<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use App\Repository\ItemRepository;
use App\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Тесты контролера заказов
 */
class OrderControllerTest extends RestTestCase
{
    /**
     * Проверка корректного респонса при создании заказа
     */
    public function testCreateOrderResponse()
    {
        /*
         * Подготовка данных для теста
         */
        $this->loadFixtures(
            [
                [
                    'name'  => 'Товар 1',
                    'price' => 100.00,
                ],
                [
                    'name'  => 'Товар 2',
                    'price' => 150.00,
                ],
            ],
            ItemRepository::TABLE_NAME
        );

        $response = $this->post(
            '/api/create-order',
            [],
            [],
            [],
            json_encode(['itemIds' => [1, 2]])
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->isOk());

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(
            [
                'id' => 1,
            ],
            $result
        );
    }

    /**
     * Проверка корректного респонса при некорректном запросе (ключ верный, не указаны id товаров)
     *
     * @param string $content Тело запроса
     *
     * @dataProvider provideTestCreateOrderBadRequestResponse
     */
    public function testCreateOrderBadRequestResponse(string $content)
    {
        $response = $this->post(
            '/api/create-order',
            [],
            [],
            [],
            $content
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Данные ревеста для testCreateOrderBadRequestResponse
     *
     * @return array
     */
    public function provideTestCreateOrderBadRequestResponse(): array
    {
        return [
            [
                json_encode(['itemIds' => []]),
            ],
            [
                json_encode([]),
            ],
        ];
    }

    /**
     * Проверка корректного респонса c указанием ошибки при некорректном запросе (нет товаров в БД)
     */
    public function testCreateOrderBadRequestWithErrorResponse()
    {
        $response = $this->post(
            '/api/create-order',
            [],
            [],
            [],
            json_encode(['itemIds' => [1]])
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(
            [
                'error' => 'Invalid input data',
            ],
            json_decode($response->getContent(), true)
        );
    }
}
