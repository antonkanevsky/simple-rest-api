<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Core\RequestAwareInterface;
use App\Service\CreateOrderServiceInterface;
use App\Service\Exception\APIServiceException;
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
     * Реквест
     *
     * @var Request
     */
    private $request;

    /**
     * Конструктор
     *
     * @param CreateOrderServiceInterface $createOrderService
     */
    public function __construct(CreateOrderServiceInterface $createOrderService)
    {
        $this->createOrderService = $createOrderService;
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
        $content = json_decode($this->request->getContent(), true);
        $itemIds = $content['itemIds'] ?? [];

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
}
