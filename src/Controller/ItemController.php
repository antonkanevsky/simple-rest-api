<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Repository\ItemRepository;
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
     * Репозиторий товаров
     *
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * Конструктор
     *
     * @param ItemGeneratorServiceInterface $itemGenerator
     * @param ItemRepository                $itemRepository
     * @param ItemViewFactory               $viewFactory
     */
    public function __construct(
        ItemGeneratorServiceInterface $itemGenerator,
        ItemRepository $itemRepository,
        ItemViewFactory $viewFactory
    ) {
        $this->itemGenerator  = $itemGenerator;
        $this->itemRepository = $itemRepository;
        $this->viewFactory    = $viewFactory;
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

    /**
     * Получение всех товаров
     *
     * @return JsonResponse
     */
    public function getItems(): JsonResponse
    {
        $items = $this->itemRepository->findAll();

        return new JsonResponse($this->viewFactory->createCollectionView($items));
    }
}
