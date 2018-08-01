<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;


abstract class InitCommon
{
    /**
     * Идентификатор терминала, выдается Продавцу Банком
     * обязательный да
     * String(20)
     *
     * @var string
     */
    protected $terminalKey;

    /**
     * Сумма в копейках
     * обязательный да
     * Number(10)
     *
     * @var int
     */
    protected $amount;

    /**
     * Номер заказа в системе Продавца
     * обязательный да
     * String(50)
     *
     * @var string
     */
    protected $orderId;

    /**
     * @return string
     */
    public function getTerminalKey(): string
    {
        return $this->terminalKey;
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
    public function getOrderId(): string
    {
        return $this->orderId;
    }
}