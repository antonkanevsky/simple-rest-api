<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Service\CreateOrderService;
use App\Tests\TestCase;

/**
 * Тест сервиса создания заказа
 */
class CreateOrderServiceTest extends TestCase
{
    /**
     * Тестируемый сервис
     *
     * @var CreateOrderService
     */
    private $createOrderService;

    /**
     * Репозиторий заказов
     *
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Репозиторий товаров
     *
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * Установка окружения
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createOrderService = $this->getContainer()->get('test.create_order_service');
        $this->orderRepository    = $this->getContainer()->get('App\Repository\OrderRepository');
        $this->itemRepository    = $this->getContainer()->get('App\Repository\ItemRepository');
    }

    /**
     * Сброс окружения
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset(
            $this->createOrderService,
            $this->orderRepository,
            $this->itemRepository
        );
    }

    /**
     * Проверка создания заказа
     */
    public function testCreateOrder()
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

        $createdOrder = $this->createOrderService->createOrder([1, 2]);

        $this->assertInstanceOf(Order::class, $createdOrder);
        $this->assertSame(Order::STATUS_NEW, $createdOrder->getStatus());
        $this->assertSame(250.00, $createdOrder->getAmount());
        $this->assertNotEmpty($createdOrder->getCreatedAt());

        $orders = $this->orderRepository->findAll();
        $this->assertCount(1, $orders);

        $order = current($orders);
        $this->assertEquals($order->getId(), $createdOrder->getId());
    }
}
