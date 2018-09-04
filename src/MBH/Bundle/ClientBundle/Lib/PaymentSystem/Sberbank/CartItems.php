<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class CartItems
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart#cartitems
 */
class CartItems implements \JsonSerializable
{
    /**
     * Элемент массива с атрибутами товарной позиции в корзине
     *
     * @var Items[]
     */
    private $items = [];

    /**
     * @return Items[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Items $items
     */
    public function addItem(Items $items): void
    {
        $this->items[] = $items;
    }

    public function jsonSerialize()
    {
        return [
            'items' => $this->getItems(),
        ];
    }
}