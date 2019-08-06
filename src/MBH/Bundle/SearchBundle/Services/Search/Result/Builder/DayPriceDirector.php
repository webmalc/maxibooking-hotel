<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use DateTime;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;

class DayPriceDirector
{
    /** @var DayPriceBuilderInterface */
    private $dayPriceBuilder;

    /**
     * DayPriceDirector constructor.
     * @param DayPriceBuilderInterface $dayPriceBuilder
     */
    public function __construct(DayPriceBuilderInterface $dayPriceBuilder)
    {
        $this->dayPriceBuilder = $dayPriceBuilder;
    }

    public function createDayPricePromotion(DateTime $date, array $tourists, RoomType $roomType, Tariff $tariff, float $dayPrice, Promotion $promotion): DayPrice
    {
        $this->buildDayPrice($date, $tourists, $roomType, $tariff, $dayPrice);
        $this->dayPriceBuilder->addDiscount([$promotion->getId() => $promotion->getDiscount()]);

        return $this->dayPriceBuilder->setPromotion($promotion->getId())->getDayPrice();
    }

    public function createDayPriceSpecial(DateTime $date, array $tourists, RoomType $roomType, Tariff $tariff, float $dayPrice, Special $special): DayPrice
    {
        $this->buildDayPrice($date, $tourists, $roomType, $tariff, $dayPrice);
        $this->dayPriceBuilder->addDiscount([$special->getId() => $special->getDiscount()]);

        return $this->dayPriceBuilder->setSpecial($special->getId())->getDayPrice();
    }


    public function createDayPrice(DateTime $date, array $tourists, RoomType $roomType, Tariff $tariff, float $dayPrice): DayPrice
    {
        $this->buildDayPrice($date, $tourists, $roomType, $tariff, $dayPrice);

        return $this->dayPriceBuilder->getDayPrice();
    }


    /** TODO: Rebuild todo  to Tourists interface */
    private function buildDayPrice(DateTime $date, array $tourists, RoomType $roomType, Tariff $tariff, float $dayPrice): void
    {
        $this->dayPriceBuilder
            ->createInstance()
            ->setDate($date)
            ->setAdults($tourists['mainAdults'])
            ->setAdditionalAdults($tourists['addsAdults'])
            ->setChildren($tourists['mainChildren'])
            ->setAdditionalChildren($tourists['addsChildren'])
            ->setRoomType($roomType->getId())
            ->setTariff($tariff->getId())
            ->setTotal($dayPrice);
    }

}