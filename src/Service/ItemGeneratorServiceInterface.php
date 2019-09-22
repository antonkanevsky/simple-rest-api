<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Item;

/**
 * Интерфейс сервиса генератора новых товаров
 */
interface ItemGeneratorServiceInterface
{
    /**
     * Генерирует новые товары
     *
     * @return Item[]
     */
    public function generateItems(): array;
}
