<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class RegisterResponse
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register
 */
class RegisterResponse
{
    /**
     * URL-адрес платёжной формы, на который нужно перенаправить браузер клиента.
     * Не возвращается, если регистрация заказа не удалась по причине ошибки, детализированной в errorCode.
     *
     * @var string
     */
    private $formUrl;

    /**
     * Код ошибки.
     *
     * @var integer
     */
    private $errorCode;

    /**
     * Номер заказа в платёжном шлюзе. Уникален в пределах шлюза.
     *
     * @var string
     */
    private $orderId;

    /**
     * Описание ошибки на языке, переданном в параметре
     *
     * @var string
     */
    private $errorMessage;
}