<?php
/**
 * Created by PhpStorm.
 * Date: 07.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;

abstract class InvoiceCommon
{
    /**
     * Идентификатор магазина
     *
     * @var string|integer
     *  Required
     */
    protected $shopID;

    /**
     * Дата и время окончания действия
     *
     * @var string
     * <date-time> Required
     */
    protected $dueDate;

    /**
     * Стоимость предлагаемых товаров или услуг, в минорных денежных единицах,
     * например в копейках в случае указания российских рублей в качестве валюты.
     *
     * @var integer
     *  <int64> >= 1 Required
     */
    protected $amount;

    /**
     * Валюта, символьный код согласно ISO 4217.
     *
     * @var string
     * Required ^[A-Z]{3}$
     */
    protected $currency;

    /**
     * Наименование предлагаемых товаров или услуг
     *
     * @var string
     *  <= 100 characters Required
     */
    protected $product;

    /**
     * Описание предлагаемых товаров или услуг
     *
     *
     *  <= 1000 characters
     */
    protected $description;

    /**
     * Корзина с набором позиций продаваемых товаров или услуг
     *
     * @var Cart[]
     */
    protected $cart;

    /**
     * Связанные с инвойсом метаданные
     *
     * @var
     * object Required
     */
    protected $metadata;

    /**
     * @return string
     */
    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Cart[]
     */
    public function getCart(): array
    {
        return $this->cart;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return null|string
     */
    public function getCashDocumentId(): ?string
    {
        return $this->getMetadata() !== null ? $this->getMetadata()['cashId'] ?? null : null;
    }
}