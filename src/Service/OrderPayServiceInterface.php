<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Order;

/**
 * Интерфейс сервиса оплаты заказа
 */
interface OrderPayServiceInterface
{
    /**
     * Оплатить заказ
     *
     * @param string|int $orderId    Идентификатор заказа
     * @param float      $paymentSum Сумма оплаты
     *
     * @return Order
     */
    public function payOrder($orderId, float $paymentSum): Order;
}
