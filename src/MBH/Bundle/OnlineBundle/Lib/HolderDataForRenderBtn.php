<?php
/**
 * Created by PhpStorm.
 * Date: 16.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


use Symfony\Component\HttpFoundation\Request;

class HolderDataForRenderBtn
{
    /**
     * @var string
     */
    private $total;

    /**
     * @var string
     */
    private $paymentSystemName;

    /**
     * @var string
     */
    private $orderId;

    public static function create(Request $request): self
    {
        $self = new self();

        foreach ($request->request->getIterator()->getArrayCopy() as $property => $value) {
            if (property_exists(self::class, $property)) {
                $self->$property = $value;
            }
        }

        return $self;
    }

    public function isValid(): bool
    {
        if (empty($this->paymentSystemName)
            || empty($this->total)
            || empty($this->orderId)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getTotal(): string
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getPaymentSystemName(): string
    {
        return $this->paymentSystemName;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }
}