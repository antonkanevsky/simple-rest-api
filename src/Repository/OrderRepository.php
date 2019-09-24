<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Core\BaseRepository;
use App\Entity\Item;
use App\Entity\Order;

/**
 * Репозиторий заказов
 *
 * @method Order|null findById($id)
 * @method Order[]    findAll()
 * @method void       save(Order $order)
 */
class OrderRepository extends BaseRepository
{
    /**
     * {@inheritdoc}
     */
    const TABLE_NAME = 'orders';

    /**
     * {@inheritdoc}
     */
    protected $fields = ['id', 'status'];

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct(Order::class);
    }

    /**
     * Добавить товары к заказу
     *
     * @param int    $orderId Id заказа
     * @param Item[] $items   Товары для добавления
     */
    public function addItemsToOrder(int $orderId, $items): void
    {
        // TODO
    }
}
