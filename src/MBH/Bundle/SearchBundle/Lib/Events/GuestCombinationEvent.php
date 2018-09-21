<?php


namespace MBH\Bundle\SearchBundle\Lib\Events;


use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationCreator;
use Symfony\Component\EventDispatcher\Event;

class GuestCombinationEvent extends Event
{
    public const CHILDREN_AGES = 'combination.childrenAges';

    /** @var Tariff */
    private $tariff;

    /** @var string */
    private $combinationType = CombinationCreator::NO_CHILDREN_AGES;

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return GuestCombinationEvent
     */
    public function setTariff(Tariff $tariff): GuestCombinationEvent
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return string
     */
    public function getCombinationType(): string
    {
        return $this->combinationType;
    }

    /**
     * @param string $combinationType
     * @return GuestCombinationEvent
     */
    public function setCombinationType(string $combinationType): GuestCombinationEvent
    {
        $this->combinationType = $combinationType;

        return $this;
    }



}