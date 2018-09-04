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
class OrderBundle implements \JsonSerializable
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
     * @var CustomerDetails
     */
    private $customerDetails;

    /**
     * Блок с атрибутами товарных позиции корзины товаров
     *
     * @var CartItems
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

    /**
     * @param CustomerDetails $customerDetails
     */
    public function setCustomerDetails(?CustomerDetails $customerDetails): void
    {
        $this->customerDetails = $customerDetails;
    }

    /**
     * @param CartItems $cartItems
     */
    public function setCartItems(CartItems $cartItems): void
    {
        $this->cartItems = $cartItems;
    }

    /**
     * @return CustomerDetails
     */
    public function getCustomerDetails(): CustomerDetails
    {
        return $this->customerDetails;
    }

    /**
     * @return CartItems
     */
    public function getCartItems(): CartItems
    {
        return $this->cartItems;
    }

    public function jsonSerialize()
    {
        return [
            'customerDetails' => $this->getCustomerDetails(),
            'cartItems'       => $this->getCartItems(),
        ];
    }


}