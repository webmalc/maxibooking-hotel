<?php
/**
 * Created by PhpStorm.
 * Date: 03.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;


abstract class Response extends InitCommon
{
    /**
     * Успешность операции
     * Да
     * bool
     *
     * @var bool
     */
    protected $success;

    /**
     * Статус транзакции
     * обязательный да
     * String(20)
     *
     * @var string
     */
    protected $status;

    /**
     * Уникальный идентификатор транзакции в системе Банка
     * обязательный да
     * Number(20)
     *
     * @var int
     */
    protected $paymentId;

    /**
     * Код ошибки, «0» - если успешно
     * обязательный да
     * String(20)
     *
     * @var string
     */
    protected $errorCode;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}