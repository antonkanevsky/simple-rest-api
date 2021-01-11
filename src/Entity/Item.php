<?php

declare(strict_types=1);

namespace App\Entity;

use App\Core\EntityInterface;

/**
 * Сущность "Товар"
 */
class Item implements EntityInterface
{
    /**
     * Идентификатор товара
     *
     * @var int
     */
    private $id;

    /**
     * Название
     *
     * @var string
     */
    private $name;

    /**
     * Цена
     *
     * @var float
     */
    private $price;

    /**
     * Получить идентификатор
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Установить id
     *
     * @param int $id
     *
     * @return Item
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Получить цену
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Установить цену
     *
     * @param float $price
     *
     * @return Item
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Получить название товара
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Установить название товара
     *
     * @param string $name
     *
     * @return Item
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
