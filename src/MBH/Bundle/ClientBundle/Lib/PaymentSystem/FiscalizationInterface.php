<?php
/**
 * Created by PhpStorm.
 * Date: 29.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


interface FiscalizationInterface
{
    public function isWithFiscalization(): bool;

    public function getTaxationRateCode();

    public function getTaxationSystemCode();
}