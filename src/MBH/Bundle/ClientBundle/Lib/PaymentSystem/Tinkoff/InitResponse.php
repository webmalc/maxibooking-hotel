<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;


class InitResponse extends InitCommon
{
    /**
     * Успешность операции
     * Да
     * bool
     * 
     * @var bool
     */
    private $success;

    /**
     * Статус транзакции
     * обязательный да
     * String(20)
     * 
     * @var string
     */
    private $status;

    /**
     * Уникальный идентификатор транзакции в системе Банка
     * обязательный да
     * Number(20)
     * 
     * @var int
     */
    private $paymentId;

    /**
     * Код ошибки, «0» - если успешно
     * обязательный да
     * String(20)
     * 
     * @var string
     */
    private $errorCode;

    /**
     * Ссылка на страницу оплаты. По умолчанию ссылка доступна в течении 24 часов.
     * обязательный нет
     * String(100)
     * 
     * @var null|string
     */
    private $paymentURL;

    /**
     * Краткое описание ошибки
     * обязательный нет
     * String
     * 
     * @var null|string
     */
    private $message;

    /**
     * Подробное описание ошибки
     * обязательный нет
     * String
     * 
     * @var null|string
     */
    private $details;

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

    /**
     * @return null|string
     */
    public function getPaymentURL(): ?string
    {
        return $this->paymentURL;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return null|string
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }
}