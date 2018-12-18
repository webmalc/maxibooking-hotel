<?php
/**
 * Created by PhpStorm.
 * Date: 03.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


trait FiscalizationTrait
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isWithFiscalization = true;

    /**
     * @return bool
     */
    public function isWithFiscalization(): bool
    {
        return $this->isWithFiscalization;
    }

    /**
     * @param bool $isWithFiscalization
     */
    public function setIsWithFiscalization(bool $isWithFiscalization): self
    {
        $this->isWithFiscalization = $isWithFiscalization;

        return $this;
    }
}