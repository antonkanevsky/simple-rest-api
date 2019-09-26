<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Exception\APIServiceException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

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
     * HTTP клиент
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * URL для http запроса
     *
     * @var string
     */
    private $payCheckURL;

    /**
     * Конструктор
     *
     * @param OrderRepository $orderRepository
     * @param ClientInterface $httpClient
     * @param string          $payCheckURL
     */
    public function __construct(
        OrderRepository $orderRepository,
        ClientInterface $httpClient,
        string $payCheckURL
    ) {
        $this->orderRepository = $orderRepository;
        $this->httpClient      = $httpClient;
        $this->payCheckURL     = $payCheckURL;
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
            throw new APIServiceException('Order not found');
        }

        if (Order::STATUS_NEW !== $order->getStatus()) {
            throw new APIServiceException('Incorrect order status');
        }

        if ($paymentSum < $order->getAmount()) {
            throw new APIServiceException('Partial payment is not implemented');
        }

        if (!$this->httpRequestIsOK()) {
            throw new APIServiceException('Http request to check payment is not OK');
        }

        $order->setStatus(Order::STATUS_PAID);
        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * Проверяет успешность http запроса
     *
     * @return bool
     */
    private function httpRequestIsOK(): bool
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->payCheckURL
            );

            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            // Умышленно тушим ошибки реквеста
        }

        return false;
    }
}
