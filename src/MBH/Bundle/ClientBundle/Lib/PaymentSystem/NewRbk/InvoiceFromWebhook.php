<?php
/**
 * Created by PhpStorm.
 * Date: 09.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


class InvoiceFromWebhook extends InvoiceCommon
{
    const STATUS = [
        "unpaid",
        "cancelled",
        "paid",
        "refunded",
        "fulfilled",
    ];

    /**
     * Идентификатор инвойса
     *
     * @var string
     * Required
     */
    private $id;

    /**
     * Идентификатор магазина
     *
     * @var string
     * <int32>
     * Required
     */
    protected $shopID;

    /**
     * Дата и время создания
     *
     * @var string
     * <date-time> Required
     */
    private $createdAt;

    /**
     * Статус инвойса
     * @var string
     * Required
     */
    private $status;

    /**
     * Причина отмены или погашения инвойса
     *
     * @var string
     */
    private $reason;

    public static function parseAndCreate(array $data)
    {
        $self = new self();

        foreach ($data as $key => $value) {
            if ($key === 'cart') {
                $self->cart = Cart::create($value);
            } else {
                if (property_exists($self, $key)) {
                    $self->$key = $value;
                }
            }
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
     * @return integer
     */
    public function getShopID(): int
    {
        return $this->shopID;
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
    public function getStatus(): string
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

}