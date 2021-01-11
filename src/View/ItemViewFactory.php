<?php

declare(strict_types=1);

namespace App\View;

use App\Entity\Item;

/**
 * Представление для товаров
 */
class ItemViewFactory
{
    /**
     * Получить представление для товара
     *
     * @param Item $item Товар
     *
     * @return array
     */
    public function createView(Item $item): array
    {
        return [
            'id'    => $item->getId(),
            'name'  => $item->getName(),
            'price' => number_format($item->getPrice() ?? 0, 2, '.', ' '),
        ];
    }

    /**
     * Получить представление для коллекции товаров
     *
     * @param Item[] $itemsCollection
     *
     * @return array
     */
    public function createCollectionView(array $itemsCollection): array
    {
        $items = [];
        foreach ($itemsCollection as $item) {
            $items[] = $this->createView($item);
        }

        return $items;
    }
}
