<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Core\EntityInterface;
use UnexpectedValueException;

/**
 * Сущность заказа
 */
class Order implements EntityInterface
{
    /**
     * Статус "Новый"
     */
    const STATUS_NEW = 'new';

    /**
     * Статус "Оплачен"
     */
    const STATUS_PAID = 'paid';

    /**
     * Статусы заказа
     *
     * @var array
     */
    private const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_PAID,
    ];

    /**
     * Идентификатор заказа
     *
     * @var int
     */
    private $id;

    /**
     * Название
     *
     * @var string
     */
    private $status;

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
     * @return Order
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Получить название товара
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Установить название товара
     *
     * @param string $status
     *
     * @return Order
     *
     * @throws UnexpectedValueException
     */
    public function setStatus(string $status): self
    {
        if (!in_array($status, self::STATUSES)) {
            throw new UnexpectedValueException(sprintf('Unknown status "%s" given', $status));
        }

        $this->status = $status;

        return $this;
    }
}
