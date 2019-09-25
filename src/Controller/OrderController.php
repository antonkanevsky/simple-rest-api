<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Core\RequestAwareInterface;
use App\Entity\Order;
use App\Service\CreateOrderServiceInterface;
use App\Service\Exception\APIServiceException;
use App\Service\OrderPayServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контролер заказов
 */
class OrderController implements RequestAwareInterface
{
    /**
     * Сервис создания заказа
     *
     * @var CreateOrderServiceInterface
     */
    private $createOrderService;

    /**
     * Сервис оплаты заказа
     *
     * @var OrderPayServiceInterface
     */
    private $orderPayService;

    /**
     * Реквест
     *
     * @var Request
     */
    private $request;

    /**
     * Конструктор
     *
     * @param CreateOrderServiceInterface $createOrderService
     * @param OrderPayServiceInterface    $orderPayService
     */
    public function __construct(
        CreateOrderServiceInterface $createOrderService,
        OrderPayServiceInterface $orderPayService
    ) {
        $this->createOrderService = $createOrderService;
        $this->orderPayService = $orderPayService;
    }

    /**
     * Установка объекта реквеста
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Создание заказа
     *
     * @return JsonResponse Возвращает id созданного заказа
     */
    public function createOrder(): JsonResponse
    {
        $input   = json_decode($this->request->getContent(), true);
        $itemIds = $input['itemIds'] ?? [];

        if (empty($itemIds)) {
            return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->createOrderService->createOrder($itemIds);

            return new JsonResponse(['id' => $order->getId()]);
        } catch (APIServiceException $e) {
            return new JsonResponse(
                [
                    'error' => $e->getMessage(),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Оплата заказа
     *
     * TODO Вынести валидацию входящих данных в слой формы
     *
     * @return JsonResponse
     */
    public function payOrder(): JsonResponse
    {
        $input   = json_decode($this->request->getContent(), true);
        $orderId = $input['id'] ?? null;
        if (null === $orderId) {
            return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
        }

        $amount = $input['amount'] ?? null;
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT);
        if (false === $amount) {
            return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->orderPayService->payOrder($orderId, $amount);
            if (Order::STATUS_PAID === $order->getStatus()) {
                return new JsonResponse(
                    [
                        'success' => true,
                    ]
                );
            }

            /*
             * Случай когда неполная оплата или HTTP запрос на ya.ru не удался
             */
            return new JsonResponse(
                [
                    'error' => 'Partial payment or HTTP request from API server not OK',
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        } catch (APIServiceException $e) {
            return new JsonResponse(
                [
                    'error' => $e->getMessage(),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
}
