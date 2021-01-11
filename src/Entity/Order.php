<?php

declare(strict_types=1);

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
    public const STATUS_NEW = 'new';

    /**
     * Статус "Оплачен"
     */
    public const STATUS_PAID = 'paid';

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
     * Дата создания
     *
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * Сумма заказа
     *
     * @var float
     */
    private $amount;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    /**
     * Получить дату создания
     *
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Установить дату создания
     *
     * @param \DateTimeInterface $createdAt
     *
     * @return Order
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Получить сумму заказа
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Установить сумму заказа
     *
     * @param float $amount
     *
     * @return Order
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
