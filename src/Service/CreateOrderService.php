<?php

declare(strict_types = 1);

namespace App\Service;

use App\Core\DBConnection;
use App\Entity\Item;
use App\Entity\Order;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Service\Exception\APIServiceException;

/**
 * Сервис создания заказа
 */
class CreateOrderService implements CreateOrderServiceInterface
{
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
     * Соединение с БД
     *
     * @var DBConnection
     */
    private $dbConnection;

    /**
     * Конструктор
     *
     * @param OrderRepository $orderRepository
     * @param ItemRepository  $itemRepository
     * @param DBConnection    $dbConnection
     */
    public function __construct(
        OrderRepository $orderRepository,
        ItemRepository $itemRepository,
        DBConnection $dbConnection
    ) {
        $this->orderRepository = $orderRepository;
        $this->itemRepository  = $itemRepository;
        $this->dbConnection    = $dbConnection;
    }

    /**
     * Создание заказа
     *
     * @param array $itemIds Идентификаторы товаров
     *
     * @return Order
     */
    public function createOrder(array $itemIds): Order
    {
        $items = [];
        foreach ($itemIds as $itemId) {
            $itemId = filter_var($itemId, FILTER_VALIDATE_INT);
            $item   = $this->itemRepository->findById($itemId);
            if ($item === null) {
                throw new APIServiceException('Invalid input data');
            }

            $items[] = $item;
        }

        try {
            $this->dbConnection->beginTransaction();

            $order = $this->createNewOrder();
            $order->setAmount($this->calculateAmount($items));

            $this->orderRepository->save($order);
            $this->orderRepository->addItemsToOrder($order->getId(), $items);

            $this->dbConnection->commit();
        } catch (\Exception $e) {
            $this->dbConnection->rollback();
            throw $e;
        }

        return $order;
    }

    /**
     * Создает новый заказ
     *
     * @return Order
     */
    private function createNewOrder(): Order
    {
        $order = new Order();
        $order->setStatus(Order::STATUS_NEW);

        return $order;
    }

    /**
     * Рассчитать сумму заказа
     *
     * @param Item[] $items
     *
     * @return float
     */
    private function calculateAmount(array $items): float
    {
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->getPrice();
        }

        return $amount;
    }
}
