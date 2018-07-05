<?php
/**
 * Created by PhpStorm.
 * Date: 09.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


use Symfony\Component\HttpFoundation\Response;

class Webhook
{
    const INVOICES_TOPIC = 'InvoicesTopic';
    const CUSTOMERS_TOPIC = 'CustomersTopic';

    const INVOICE_CREATED = 'InvoiceCreated';
    const INVOICE_PAID = 'InvoicePaid';
    const INVOICE_CANCELLED = 'InvoiceCancelled';
    const INVOICE_FULFILLED = 'InvoiceFulfilled';
    const PAYMENT_STARTED = 'PaymentStarted';
    const PAYMENT_PROCESSED = 'PaymentProcessed';
    const PAYMENT_CAPTURED = 'PaymentCaptured';
    const PAYMENT_CANCELLED = 'PaymentCancelled';
    const PAYMENT_REFUNDED = 'PaymentRefunded';
    const PAYMENT_FAILED = 'PaymentFailed';
    const REFUND_CREATED = 'RefundCreated';
    const REFUND_PENDING = 'RefundPending';
    const REFUND_SUCCEEDED = 'RefundSucceeded';
    const REFUND_FAILED = 'RefundFailed';
    const CUSTOMER_CREATED = 'CustomerCreated';
    const CUSTOMER_DELETED = 'CustomerDeleted';
    const CUSTOMER_READY = 'CustomerReady';
    const CUSTOMER_BINDING_STARTED = 'CustomerBindingStarted';
    const CUSTOMER_BINDING_SUCCEEDED = 'CustomerBindingSucceeded';
    const CUSTOMER_BINDING_FAILED = 'CustomerBindingFailed';

    const EVENT_TYPE = [
        self::INVOICE_CREATED,
        self::INVOICE_PAID,
        self::INVOICE_CANCELLED,
        self::INVOICE_FULFILLED,
        self::PAYMENT_STARTED,
        self::PAYMENT_PROCESSED,
        self::PAYMENT_CAPTURED,
        self::PAYMENT_CANCELLED,
        self::PAYMENT_REFUNDED,
        self::PAYMENT_FAILED,
        self::REFUND_CREATED,
        self::REFUND_PENDING,
        self::REFUND_SUCCEEDED,
        self::REFUND_FAILED,
        self::CUSTOMER_CREATED,
        self::CUSTOMER_DELETED,
        self::CUSTOMER_READY,
        self::CUSTOMER_BINDING_STARTED,
        self::CUSTOMER_BINDING_SUCCEEDED,
        self::CUSTOMER_BINDING_FAILED,
    ];

    /**
     * Идентификатор события в системе
     *
     * @var integer
     * Required
     */
    private $eventID;

    /**
     * Дата и время возникновения события
     *
     * @var string
     * <date-time> Required
     */
    private $occuredAt;

    /**
     * Предмет оповещения
     *
     * @var string Required
     */
    private $topic;

    /**
     * Тип произошедшего с предметом оповещения события
     *
     * @var string Required
     */
    private $eventType;

    /**
     * Данные инвойса
     *
     * @var InvoiceFromWebhook|null
     * Required
     */
    private $invoice;

    public static function parseAndCreate($content)
    {
        $self = new self();

        foreach ($content as $key => $value) {
            if ($key === 'invoice') {
                $self->invoice = InvoiceFromWebhook::parseAndCreate($value);
            } else {
                if (property_exists($self, $key)) {
                    $self->$key = $value;
                }
            }
        }

        return $self;
    }

    /**
     * @return int
     */
    public function getEventID(): int
    {
        return $this->eventID;
    }

    /**
     * @return string
     */
    public function getOccuredAt(): string
    {
        return $this->occuredAt;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @return InvoiceFromWebhook
     */
    public function getInvoice(): ?InvoiceFromWebhook
    {
        return $this->invoice;
    }


}