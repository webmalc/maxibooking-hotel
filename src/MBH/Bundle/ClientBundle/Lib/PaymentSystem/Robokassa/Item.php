<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Robokassa;

/**
 * данных о позициях чека
 * https://docs.robokassa.ru/?&_ga=2.243009669.606957235.1530513783-464963976.1530513783#6865
 *
 * Class Item
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Robokassa
 */
class Item implements \JsonSerializable
{
    /**
     * Обязательное поле. Наименование товара. Строка, максимальная длина 64 символа
     * @var string
     */
    private $name;

    /**
     * Обязательное поле.
     * Полная сумма в рублях за все количество данного товара с учетом всех возможных скидок, бонусов и специальных цен.
     * Десятичное число: целая часть не более 8 знаков, дробная часть не более 2 знаков.
     *
     * @var string
     */
    private $sum;

    /**
     * Обязательное поле. Количество/вес конкретной товарной позиции.
     * Десятичное число: целая часть не более 8 знаков, дробная часть не более 3 знаков.
     *
     * @var string
     */
    private $quantity;

    /**
     * @var string
     */
    private $tax;

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
     * @return string
     */
    public function getSum(): string
    {
        return $this->sum;
    }

    /**
     * @param string $sum
     */
    public function setSum(string $sum): void
    {
        $this->sum = $sum;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;
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

    public function jsonSerialize()
    {
        return [
            'name'     => $this->getName(),
            'sum'      => $this->getSum(),
            'quantity' => $this->getQuantity(),
            'tax'      => $this->getTax(),
        ];
    }
}