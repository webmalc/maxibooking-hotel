<?php


namespace MBH\Bundle\PriceBundle\Services\SpecialBatch;


use MBH\Bundle\PriceBundle\Lib\SpecialBatcherException;
use MBH\Bundle\PriceBundle\Lib\SpecialBatchHolder;


interface SpecialBatchInterface
{
    /**
     * @param SpecialBatchHolder $holder
     * @throws SpecialBatcherException
     */
    public function applyBatch(SpecialBatchHolder $holder): void;
}