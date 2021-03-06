<?php


namespace MBH\Bundle\PackageBundle\Services;

class CalculationRounded extends Calculation
{
    /** @var  integer */
    private $sign = 2;

    public function setRoundedSign(int $sign)
    {
        $this->sign = $sign;
    }

    protected function getTotalPrice($total)
    {
        return round($total, $this->sign, PHP_ROUND_HALF_UP);
    }

}