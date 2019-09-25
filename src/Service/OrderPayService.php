<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Exception\APIServiceException;

/**
 * Сервис оплаты заказа
 */
class OrderPayService implements OrderPayServiceInterface
{
    /**
     * Репозиторий заказов
     *
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Конструктор
     *
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Оплатить заказ
     *
     * @param string|int $orderId Идентификатор заказа
     * @param float $paymentSum Сумма оплаты
     *
     * @return Order
     */
    public function payOrder($orderId, float $paymentSum): Order
    {
        $order = $this->orderRepository->findById($orderId);
        if (null === $order) {
            throw new APIServiceException('Invalid input data');
        }

        if (Order::STATUS_NEW !== $order->getStatus()) {
            throw new APIServiceException('Incorrect order status');
        }

        if ($order->getAmount() !== $paymentSum) {
            return $order;
        }

        return $order;
    }
}
