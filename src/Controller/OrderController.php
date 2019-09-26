<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Core\RequestAwareInterface;
use App\Service\CreateOrderServiceInterface;
use App\Service\Exception\APIServiceException;
use App\Service\OrderPayServiceInterface;
use App\Validator\ValidatorException;
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
     * @return JsonResponse
     */
    public function payOrder(): JsonResponse
    {
        try {
            list($orderId, $paymentSum) = $this->validatePayOrderInputData();
            $this->orderPayService->payOrder($orderId, $paymentSum);

            return new JsonResponse(
                [
                    'success' => true,
                ]
            );
        } catch (APIServiceException | ValidatorException $e) {
            return new JsonResponse(
                [
                    'error' => $e->getMessage(),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Валидация входящих данных для метода оплаты заказа
     * TODO Вынести валидацию входящих данных в отдельный класс
     *
     * @return array
     *
     * @throws ValidatorException
     */
    private function validatePayOrderInputData(): array
    {
        $input   = json_decode($this->request->getContent(), true);
        $orderId = $input['id'] ?? null;
        $amount  = $input['amount'] ?? null;

        if (null === $orderId || null === $amount) {
            throw new ValidatorException('Required fields are expected');
        }

        $amount  = filter_var($amount, FILTER_VALIDATE_FLOAT);
        $orderId = filter_var(
            $orderId,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => ['regexp' => '/^\d+$/']
            ]
        );

        if (false === $orderId || false === $amount) {
            throw new ValidatorException('Invalid input data');
        }

        return [$orderId, $amount];
    }
}
