<?php


namespace MBH\Bundle\PackageBundle\Services;


class MagicCalculationRounded extends MagicCalculation
{
    /** @var  integer */
    private $sign = 2;

    public function setRoundedSign(int $sign)
    {
        $this->sign = $sign;
    }

    protected function getTotalPrice($total)
    {
        return round($total, $this->sign);
    }

}