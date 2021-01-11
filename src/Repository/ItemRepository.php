<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\BaseRepository;
use App\Entity\Item;

/**
 * Репозиторий товаров
 *
 * @method Item|null findById(mixed $id)
 * @method Item[]    findAll()
 * @method void      save(Item $item)
 */
class ItemRepository extends BaseRepository
{
    /**
     * {@inheritdoc}
     */
    public const TABLE_NAME = 'items';

    /**
     * {@inheritdoc}
     */
    protected const FIELDS_TYPE_MAPPING = [
        'id'    => self::COLUMN_TYPE_INT,
        'name'  => self::COLUMN_TYPE_STRING,
        'price' => self::COLUMN_TYPE_FLOAT,
    ];

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
