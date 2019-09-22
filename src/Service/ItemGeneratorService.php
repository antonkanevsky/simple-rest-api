<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemRepository;

/**
 * Сервис генератор новых товаров
 */
class ItemGeneratorService implements ItemGeneratorServiceInterface
{
    /**
     * Лимит товаров для генерации
     */
    private const LIMIT = 5;

    /**
     * Репозиторий товаров
     *
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * Конструктор
     *
     * @param ItemRepository $itemRepository
     */
    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Генерирует новые товары
     *
     * @return Item[]
     */
    public function generateItems(): array
    {
        $items = [];
        $price = 100;
        for ($i = 1; $i <= self::LIMIT; $i++) {
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
