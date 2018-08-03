<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;



class InitResponse extends Response
{
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

    public static function parseResponse(\Psr\Http\Message\ResponseInterface $response): ?self
    {
        $body = json_decode($response->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($body)) {
            return null;
        }
        $self = new self();
        foreach ($body as $key => $value) {
            if (property_exists(self::class, lcfirst($key))) {
                $self->$key = $value;
            }
        }

        return $self;
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