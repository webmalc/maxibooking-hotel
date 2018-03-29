<?php


namespace MBH\Bundle\PriceBundle\Lib;


use MBH\Bundle\PriceBundle\Document\Special;

class SpecialBatcherHolder
{
    /** @var array|Special[] */
    protected $specials = [];

    protected $promotion;


    /**
     * @return array|Special[]
     */
    public function getSpecials(): array
    {
        return $this->specials;
    }

    /**
     * @param array $specials
     */
    public function setSpecials(array $specials): void
    {
        $this->specials = $specials;
    }

    /**
     * @return mixed
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param mixed $promotion
     */
    public function setPromotion($promotion): void
    {
        $this->promotion = $promotion;
    }




}