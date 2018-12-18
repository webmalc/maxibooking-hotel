<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


interface TaxMapInterface
{
    public function getTaxSystemMap(): array;

    public function getTaxRateMap(): array;
}