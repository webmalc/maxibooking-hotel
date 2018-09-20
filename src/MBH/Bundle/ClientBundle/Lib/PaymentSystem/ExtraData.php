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
     * @return HolderNamePaymentSystem[]
     */
    public function getPaymentSystemsAsObj(): array
    {
        $holder = [];
        foreach ($this->getPaymentSystems() as $key => $name) {
            $holder[] = $this->getPaymentSystemAsObj($key);
        }

        return $holder;
    }

    /**
     * @param string $key
     * @return HolderNamePaymentSystem
     */
    public function getPaymentSystemAsObj(string $key): HolderNamePaymentSystem
    {
        $name = $this->getPaymentSystems()[$key];
        /** т.к. там используется перевод */
        if ($key === 'invoice') {
            $name = 'Invoice';
        }

        return new HolderNamePaymentSystem($key, $name);
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
     * @param TaxMapInterface|null $paymentSystem
     * @return array
     */
    public function getTaxationRateCodes(TaxMapInterface $paymentSystem = null): array
    {
        if ($paymentSystem === null) {
            return $this->taxationRateCodes['rate_codes'];
        }

        return $this->map($paymentSystem->getTaxRateMap(),$this->getTaxationRateCodes());
    }

    /**
     * @param TaxMapInterface|null $paymentSystem
     * @return array
     */
    public function getTaxationSystemCodes(TaxMapInterface $paymentSystem = null): array
    {
        if ($paymentSystem === null) {
            return $this->taxationRateCodes['system_codes'];
        }

        return $this->map($paymentSystem->getTaxSystemMap(), $this->getTaxationSystemCodes());
    }

    /**
     * @param array $externalDataMap
     * @param array $interiorData
     * @return array
     */
    private function map(array $externalDataMap, array  $interiorData): array
    {
        $externalData = [];
        foreach ($externalDataMap as $interior => $external) {
            if (!empty($interiorData[$interior])) {
                $externalData[$external] = $interiorData[$interior];
            }
        }

        return $externalData;
    }
}