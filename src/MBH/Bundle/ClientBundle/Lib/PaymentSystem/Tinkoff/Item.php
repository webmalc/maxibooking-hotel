<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;

/**
 * @see https://oplata.tinkoff.ru/landing/develop/documentation
 *
 * Class Item
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff
 */
class Item implements \JsonSerializable
{
    /**
     * Наименование товара. Максимальная длина строки – 128 символов
     *
     * обязательное да
     *
     * @var string
     */
    private $name;

    /**
     * Цена в копейках. *Целочисленное значение не более 10 знаков
     *
     * обязательное да
     *
     * @var int
     */
    private $price;

    /**
     * Количество/вес:
     *   целая часть не более 8 знаков;
     *   дробная часть не более 3 знаков
     *
     * обязательное да
     *
     * @var float
     */
    private $quantity;

    /**
     * Сумма в копейках. Целочисленное значение не более 10 знаков
     *
     * обязательное да
     *
     * @var int
     */
    private $amount;

    /**
     * Ставка налога
     *
     * обязательное да
     *
     * @var string
     */
    private $tax;

    /**
     * Штрих-код
     *
     * обязательное нет
     * String(20)
     *
     * @var null|string
     */
    private $ean13;

    /**
     * Код магазина
     *
     * обязательное нет
     * String(64)
     *
     * @var null|string
     */
    private $shopCode;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getTax(): string
    {
        return $this->tax;
    }

    /**
     * @param string $tax
     */
    public function setTax(string $tax): void
    {
        $this->tax = $tax;
    }

    /**
     * @return null|string
     */
    public function getEan13(): ?string
    {
        return $this->ean13;
    }

    /**
     * @param null|string $ean13
     */
    public function setEan13(?string $ean13): void
    {
        $this->ean13 = $ean13;
    }

    /**
     * @return null|string
     */
    public function getShopCode(): ?string
    {
        return $this->shopCode;
    }

    /**
     * @param null|string $shopCode
     */
    public function setShopCode(?string $shopCode): void
    {
        $this->shopCode = $shopCode;
    }

    public function jsonSerialize()
    {
        $data = [
            'Name'     => $this->getName(),
            'Price'    => $this->getPrice(),
            'Quantity' => $this->getQuantity(),
            'Amount'   => $this->getAmount(),
            'Tax'      => $this->getTax(),
        ];

        if ($this->getEan13() !== null) {
            $data['Ean13'] = $this->getEan13();
        }

        if ($this->getShopCode() !== null) {
            $data['ShopCode'] = $this->getShopCode();
        }

        return $data;
    }
}