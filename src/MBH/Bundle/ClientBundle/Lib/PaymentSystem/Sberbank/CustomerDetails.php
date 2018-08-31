<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class CustomerDetails
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart#customerDetails
 */
class CustomerDetails
{
    /**
     * Адрес электронной почты покупателя.
     *
     * @var string
     */
    private $email;

    /**
     * Номер телефона покупателя.
     *
     * @var integer
     */
    private $phone;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Способ связи с покупателем.
     *
     * @var string
     */
    private $contact;
    
    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Блок с атрибутами адреса для доставки
     *
     * @var 
     */
    private $deliveryInfo;
}