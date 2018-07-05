<?php
/**
 * Created by PhpStorm.
 * Date: 22.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


class ExtraData
{
    /**
     * @var array
     */
    private $paymentSystems;

    /**
     * @var bool
     */
    private $paymentSystemsChange;

    /**
     * @var string
     */
    private $paymentSystemsDefault;

    /**
     * @var array
     */
    private $taxationRateCodes;

    public function __construct(array $paymentSystems, $paymentSystemsChange, $paymentSystemsDefault, $taxationRateCodes)
    {
        $this->paymentSystems = $paymentSystems;
        $this->paymentSystemsChange = $paymentSystemsChange;
        $this->paymentSystemsDefault = $paymentSystemsDefault;
        $this->taxationRateCodes = $taxationRateCodes;
    }

    /**
     * @return array
     */
    public function getPaymentSystems(): array
    {
        return $this->paymentSystems;
    }

    /**
     * @return bool
     */
    public function isPaymentSystemsChange(): bool
    {
        return $this->paymentSystemsChange;
    }

    /**
     * @return string
     */
    public function getPaymentSystemsDefault(): string
    {
        return $this->paymentSystemsDefault;
    }

    /**
     * @return array
     */
    public function getTaxationRateCodes(): array
    {
        return $this->taxationRateCodes;
    }
}