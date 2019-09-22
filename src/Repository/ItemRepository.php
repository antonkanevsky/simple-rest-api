<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Core\BaseRepository;
use App\Entity\Item;

/**
 * Репозиторий товаров
 */
class ItemRepository extends BaseRepository
{
    /**
     * {@inheritdoc}
     */
    const TABLE_NAME = 'items';

    /**
     * {@inheritdoc}
     */
    protected $fields = ['id', 'name', 'price'];

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct(Item::class);
    }
}
