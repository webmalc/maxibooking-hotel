<?php


namespace MBH\Bundle\PriceBundle\Lib;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;

class SpecialBatchHolder
{
    /** @var array|Special[] */
    protected $specials = [];

     /** @var Promotion */
    protected $promotion;

    /** @var Tariff */
    protected $tariff;


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
    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    /**
     * @param mixed $promotion
     */
    public function setPromotion(Promotion $promotion): void
    {
        $this->promotion = $promotion;
    }

    public function getSpecialIds(): array
    {
        $result = [];
        if (null !== $this->specials) {
            foreach ($this->specials as $special) {
                $result[] = $special->getId();
            }
        }

        return $result;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     */
    public function setTariff(Tariff $tariff): void
    {
        $this->tariff = $tariff;
    }


}