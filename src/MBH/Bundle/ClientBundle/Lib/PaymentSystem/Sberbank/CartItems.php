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
class CartItems
{
    /**
     * Элемент массива с атрибутами товарной позиции в корзине
     *
     * @var
     */
    private $items = [];
}