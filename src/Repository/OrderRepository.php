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
    const FIELDS_TYPE_MAPPING = [
        'id'         => self::COLUMN_TYPE_INT,
        'created_at' => self::COLUMN_TYPE_DATE_TIME,
        'amount'     => self::COLUMN_TYPE_FLOAT,
    ];

    /**
     * {@inheritdoc}
     */
    protected $fields = ['id', 'status', 'created_at', 'amount'];

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
        $sql = "INSERT INTO order_item (order_id, item_id) VALUES ($orderId, ?)";

        $stmt = $this->dbConnection->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([$item->getId()]);
        }
    }
}
