<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\ItemRepository;
use App\Service\OrderPayService;
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
            '/api/order/create',
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
     * @dataProvider provideTestCreateOrderWithBadRequestResponse
     */
    public function testCreateOrderWithBadRequestResponse(string $content)
    {
        $response = $this->post(
            '/api/order/create',
            [],
            [],
            [],
            $content
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Данные ревеста для testCreateOrderWithBadRequestResponse
     *
     * @return array
     */
    public function provideTestCreateOrderWithBadRequestResponse(): array
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
            '/api/order/create',
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

    /**
     * Проверка 400 ответа в случае невалидности данных запроса
     *
     * @param string $content          Тело запроса
     * @param array  $expectedResponse Ожидаемый ответ
     *
     * @dataProvider provideTestPayOrderBadRequestResponse
     */
    public function testPayOrderBadRequestResponse(string $content, array $expectedResponse)
    {
        $response = $this->post(
            '/api/order/pay',
            [],
            [],
            [],
            $content
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true));
    }

    /**
     * Провайдер данных для testPayOrderBadRequestResponse
     *
     * @return array
     */
    public function provideTestPayOrderBadRequestResponse(): array
    {
        return [
            [
                json_encode([]),
                [
                    'error' => 'Required fields are expected',
                ],
            ],
            [
                json_encode(['id' => '12']),
                [
                    'error' => 'Required fields are expected',
                ],
            ],
            [
                json_encode(['amount' => '4200.0']),
                [
                    'error' => 'Required fields are expected',
                ],
            ],
            [
                json_encode([
                    'id'     => '12s',
                    'amount' => '4200.0',
                ]),
                [
                    'error' => 'Invalid input data',
                ],
            ],
            [
                json_encode([
                    'id'     => '1',
                    'amount' => '1200some string.0',
                ]),
                [
                    'error' => 'Invalid input data',
                ],
            ],
        ];
    }

    /**
     * Проверка корректности ответа при успешной оплате заказа
     */
    public function testPayOrderSuccessResponse()
    {
        /*
         * Подменяем инстанс сервиса моком для эмуляции успешной оплаты
         */
        $payOrderServiceMock = $this->getMockBuilder(OrderPayService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setContainerDependency('test.pay_order_service', $payOrderServiceMock);

        $response = $this->post(
            '/api/order/pay',
            [],
            [],
            [],
            json_encode([
                'id'     => '1',
                'amount' => '300.00',
            ])
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->isOk());

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(
            [
                'success' => true,
            ],
            $result
        );
    }
}
