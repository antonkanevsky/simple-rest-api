<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemRepository;

/**
 * Сервис генератор новых товаров
 */
class ItemGeneratorService implements ItemGeneratorServiceInterface
{
    /**
     * Лимит товаров для генерации (по умолчанию)
     */
    private const DEFAULT_LIMIT = 20;

    /**
     * Репозиторий товаров
     *
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * Лимит товаров для генерации
     *
     * @var int
     */
    private $limit;

    /**
     * Конструктор
     *
     * @param ItemRepository $itemRepository
     * @param int            $limit
     */
    public function __construct(ItemRepository $itemRepository, int $limit = self::DEFAULT_LIMIT)
    {
        $this->itemRepository = $itemRepository;
        $this->limit = $limit;
    }

    /**
     * Генерирует новые товары
     *
     * @param int $limit
     *
     * @return Item[]
     */
    public function generateItems(): array
    {
        $items = [];
        $price = 100;
        for ($i = 1; $i <= $this->limit; $i++) {
            $item = new Item();
            $item
                ->setName('Товар ' . $i)
                ->setPrice($price);

            $this->itemRepository->save($item);
            $items[] = $item;

            $price += 50;
        }

        return $items;
    }
}
