<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;

/**
 * Class InvoiceResponse
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk
 *
 * @see https://v1.api.developer.rbk.money/#operation/createInvoice
 */
class InvoiceResponse extends InvoiceCommon
{
    /**
     * Идентификатор инвойса
     *
     * @var string
     * Required
     */
    private $id;

    /**
     * Дата и время создания
     *
     * @var string
     * <date-time> Required
     */
    private $createdAt;

    /**
     * Идентификатор шаблона (для инвойсов, созданных по шаблону).
     *
     * @var string
     */
    private $invoiceTemplateID;

    /**
     * @var
     * string Required
     * "unpaid" "cancelled" "paid" "fulfilled"
     * Статус инвойса
     */
    private $status;

    /**
     * @var string <= 1000 characters
     * Причина отмены или погашения инвойса
     */
    private $reason;

    /**
     * Содержимое токена для доступа
     * @var string
     *  Required
     */
    private $invoiceAccessToken;

    /**
     * @var boolean
     */
    private $success = true;

    /**
     * @var Error
     */
    private $error;

    /**
     * @param array $data
     * @return InvoiceResponse
     */
    public static function load(array $data)
    {
        $self = new self();

        if (isset($data['invoice'])) {

            foreach ($data['invoice'] as $key => $value) {
                if ($key === 'cart') {
                    $self->cart = Cart::create($value);
                } else {
                    if (property_exists($self, $key)) {
                        $self->$key = $value;
                    }
                }
            }

            $self->invoiceAccessToken = $data['invoiceAccessToken']['payload'];

        } else {
            $self->success = false;
            $self->error = Error::instance($data);
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getInvoiceTemplateID(): string
    {
        return $this->invoiceTemplateID;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getInvoiceAccessToken(): string
    {
        return $this->invoiceAccessToken;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return Error
     */
    public function getError(): Error
    {
        return $this->error;
    }
}