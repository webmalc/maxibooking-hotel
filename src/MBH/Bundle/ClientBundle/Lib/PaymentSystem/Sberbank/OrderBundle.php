<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class OrderBundle
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart#orderbundle
 */
class OrderBundle
{
    /**
     * Дата создания заказа.
     *
     * @var string
     */
    private $orderCreationDate;

    /**
     * Блок с атрибутами данных о покупателе
     *
     * @var
     */
    private $customerDetails;

    /**
     * Блок с атрибутами товарных позиции корзины товаров
     *
     * @var
     */
    private $cartItems;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Блок с атрибутами бонусных программ, в которых участвуют товарные позиции из корзины.
     *
     * @var
     */
    private $loyalties;
}