<?php
/**
 * Created by PhpStorm.
 * Date: 03.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

trait FiscalizationTrait
{
    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    protected $isWithFiscalization = false;

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
