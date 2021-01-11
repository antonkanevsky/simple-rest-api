<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;

/**
 * Интерфейс сервиса создания заказа
 */
interface CreateOrderServiceInterface
{
    /**
     * Создание заказа
     *
     * @param array $itemIds Идентификаторы товаров
     *
     * @return Order
     */
    public function createOrder(array $itemIds): Order;
}
