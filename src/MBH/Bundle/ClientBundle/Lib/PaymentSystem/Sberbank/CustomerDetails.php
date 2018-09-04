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
class CustomerDetails implements \JsonSerializable
{
    /**
     * Адрес электронной почты покупателя.
     *
     * @var null|string
     */
    private $email;

    /**
     * Номер телефона покупателя.
     *
     * @var null|integer
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

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return int|null
     */
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    /**
     * @param int|null $phone
     */
    public function setPhone(?int $phone): void
    {
        $this->phone = $phone;
    }

    public function jsonSerialize()
    {
        return [
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
        ];
    }
}