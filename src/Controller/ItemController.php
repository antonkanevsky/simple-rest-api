<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\ItemGeneratorServiceInterface;
use App\View\ItemViewFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Контролер товаров
 */
class ItemController
{
    /**
     * Сервис генератор новых товаров
     *
     * @var ItemGeneratorServiceInterface
     */
    private $itemGenerator;

    /**
     * Представление для товаров
     *
     * @var ItemViewFactory
     */
    private $viewFactory;

    /**
     * Конструктор
     *
     * @param ItemGeneratorServiceInterface $itemGenerator
     * @param ItemViewFactory $viewFactory
     */
    public function __construct(
        ItemGeneratorServiceInterface $itemGenerator,
        ItemViewFactory $viewFactory
    ) {
        $this->itemGenerator = $itemGenerator;
        $this->viewFactory   = $viewFactory;
    }

    /**
     * Генерация товаров
     *
     * @return JsonResponse
     */
    public function createItems(): JsonResponse
    {
        $items = $this->itemGenerator->generateItems();

        return new JsonResponse($this->viewFactory->createCollectionView($items));
    }
}
