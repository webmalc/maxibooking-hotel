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
    const NO_ERROR = 0;

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

    public static function parseResponse(\Psr\Http\Message\ResponseInterface $response): ?self
    {
        $body = json_decode($response->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($body)) {
            return null;
        }
        $self = new self();
        foreach ($body as $key => $value) {
            $key = lcfirst($key);
            if (property_exists(self::class, $key)) {
                $self->$key = $value;
            }
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getFormUrl(): string
    {
        return $this->formUrl;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}