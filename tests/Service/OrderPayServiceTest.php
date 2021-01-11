<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Exception\APIServiceException;
use App\Service\OrderPayService;
use App\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Тесты сервиса оплаты заказа
 */
class OrderPayServiceTest extends TestCase
{
    /**
     * Репозиторий заказов
     *
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Установка окружения
     */
    protected function setUp()
    {
        parent::setUp();

        $this->orderRepository = $this->getContainer()->get('App\Repository\OrderRepository');
    }

    /**
     * Сброс окружения
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->orderRepository);
    }

    /**
     * Проверка невозможности оплаты несуществующего заказа
     */
    public function testPayOrderWithNotFoundIdWouldThrowException()
    {
        $this->expectException(APIServiceException::class);
        $this->expectExceptionMessage('Order not found');

        $orderPayService = $this->getOrderPayService(new Client());
        $orderPayService->payOrder(1, 200.00);
    }

    /**
     * Проверка невозможности оплаты заказа в некорректном статусе
     */
    public function testPayOrderInIncorrectStatusWouldThrowException()
    {
        $this->expectException(APIServiceException::class);
        $this->expectExceptionMessage('Incorrect order status');

        /*
         * Подготовка данных для теста
         */
        $this->loadFixtures(
            [
               [
                   'status'     => Order::STATUS_PAID,
                   'amount'     => '200.00',
                   'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
               ]
            ],
            OrderRepository::TABLE_NAME
        );

        $orderPayService = $this->getOrderPayService(new Client());
        $orderPayService->payOrder(1, 200.00);
    }

    /**
     * Проверка невозможности частичной оплаты заказа
     */
    public function testPayOrderPartiallyWouldThrowException()
    {
        $this->expectException(APIServiceException::class);
        $this->expectExceptionMessage('Partial payment is not implemented');

        /*
         * Подготовка данных для теста
         */
        $this->loadFixtures(
            [
                [
                    'status'     => Order::STATUS_NEW,
                    'amount'     => '200.00',
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ]
            ],
            OrderRepository::TABLE_NAME
        );

        $orderPayService = $this->getOrderPayService(new Client());
        $orderPayService->payOrder(1, 199.00);
    }

    /**
     * Проверка успешной оплаты в случае полной оплаты и успешного http запроса
     */
    public function testPayOrderWhenEnoughMoneyAndHttpRequestIsOK()
    {
        /*
         * Подготовка данных для теста
         */
        $this->loadFixtures(
            [
                [
                    'status'     => Order::STATUS_NEW,
                    'amount'     => '200.00',
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ]
            ],
            OrderRepository::TABLE_NAME
        );

        /*
         * Подменяем инстанс http клиента моком для эмуляции успешного респонса
         */
        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', $this->getContainer()->getParameter('order.pay_check_url'))
            ->willReturn(
                $this->createConfiguredMock(
                    Response::class,
                    [
                        'getStatusCode' => 200
                    ]
                )
            );

        $orderPayService = $this->getOrderPayService($httpClientMock);
        $orderPayService->payOrder(1, 200.00);

        /*
         * Проверка смены статуса
         */
        $order = $this->orderRepository->findById(1);
        $this->assertEquals(Order::STATUS_PAID, $order->getStatus());
    }

    /**
     * Проверка исключения в случае полной оплаты и неудачного http запроса
     */
    public function testPayOrderWhenEnoughMoneyAndHttpRequestIsNotOK()
    {
        $this->expectException(APIServiceException::class);
        $this->expectExceptionMessage('Http request to check payment is not OK');

        /*
         * Подготовка данных для теста
         */
        $this->loadFixtures(
            [
                [
                    'status'     => Order::STATUS_NEW,
                    'amount'     => '200.00',
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ]
            ],
            OrderRepository::TABLE_NAME
        );

        /*
         * Подменяем инстанс http клиента моком для эмуляции неуспешного респонса
         */
        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', $this->getContainer()->getParameter('order.pay_check_url'))
            ->willReturn(
                $this->createConfiguredMock(
                    Response::class,
                    [
                        'getStatusCode' => 404
                    ]
                )
            );

        $orderPayService = $this->getOrderPayService($httpClientMock);
        $orderPayService->payOrder(1, 200.00);
    }

    /**
     * Получить инстанс тестируемого сервиса.
     * Вынесено в отдельный метод из за невозможности подмены http клиента при получении тестируемого сервиса
     * из DI контейнера.
     *
     * @param Client|MockObject $httpClient
     *
     * @return OrderPayService
     */
    private function getOrderPayService(Client $httpClient): OrderPayService
    {
        return new OrderPayService(
            $this->orderRepository,
            $httpClient,
            $this->getContainer()->getParameter('order.pay_check_url')
        );
    }
}
