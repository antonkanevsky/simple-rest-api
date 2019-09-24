<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\CreateOrderServiceInterface;
use App\Service\Exception\APIServiceException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контролер заказов
 */
class OrderController
{
    /**
     * Сервис создания заказа
     *
     * @var CreateOrderServiceInterface
     */
    private $createOrderService;

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
     * Создание заказа
     *
     * @return JsonResponse
     */
    public function createOrder(): JsonResponse
    {
        $request = Request::createFromGlobals();
        $content = json_decode($request->getContent(), true);
        $itemIds = $content['itemIds'] ?? [];

        if (empty($itemIds)) {
            return new JsonResponse(null, JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->createOrderService->createOrder($itemIds);

            return new JsonResponse([
                'id' => 'asasdasds'
            ]);
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
