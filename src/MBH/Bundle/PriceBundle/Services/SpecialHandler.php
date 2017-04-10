<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Services\Calculation;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;
use MBH\Bundle\PriceBundle\Document\Tariff;

class SpecialHandler
{
    /** @var SearchFactory $search */
    private $search;
    /** @var  DocumentManager */
    private $dm;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Calculation
     */
    private $calc;

    /**
     * SpecialHandler constructor.
     * @param SearchFactory $search
     * @param DocumentManager $dm
     * @param Helper $helper
     */
    public function __construct(SearchFactory $search, DocumentManager $dm, Helper $helper, Calculation $calc)
    {
        $this->dm = $dm;
        $this->search = $search;

        $this->helper = $helper;
        $this->calc = $calc;
    }


    /**
     * @param array $specialIds
     * @param array $roomTypeIds
     * @return void
     */
    public function calculatePrices(array $specialIds = [], array $roomTypeIds = []): void
    {


        $specials = $this->getSpecials($specialIds);
        $currentDate = new \DateTime('midnight');

        /** @var Special $special */
        foreach ($specials as $special) {
            $special->setRecalculation();
            $this->dm->flush();
            $special->removeAllPrices();
            if ($special->getIsEnabled()
                && $special->getRemain() > 0
                && $special->getBegin() > $currentDate
                && $special->getDisplayFrom() <= $currentDate
                && $special->getDisplayTo() >= $currentDate
            ) {
                $roomTypes = $this->getRoomTypes($special);
                $tariffs = $this->getTariffs($special);
                foreach ($roomTypes as $roomType) {
                    /** @var RoomType $roomType */
                    foreach ($tariffs as $tariff) {
                        /** @var Tariff $tariff */
                        $specialPrice = $this->calculateSpecialPrice($special, $roomType, $tariff);
                        if ($specialPrice) {
                            $special->addPrice($specialPrice);
                        }
                    }
                }
            }

            $special->setNoRecalculation();
            $this->dm->flush();
        }
    }

    private function getRoomTypes(Special $special)
    {
        $roomTypes = $special->getRoomTypes();
        if (!count($roomTypes)) {
            $hotel = $special->getHotel();
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel);
        }

        return $roomTypes;
    }

    private function getTariffs(Special $special)
    {
        $tariffs = $special->getTariffs();
        if (!count($tariffs)) {
            $hotel = $special->getHotel();
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch($hotel, null, true);
        }

        return $tariffs;
    }

    private function calculateSpecialPrice(Special $special, RoomType $roomType, Tariff $tariff): ?SpecialPrice
    {
        $begin = clone $special->getBegin();
        $end = (clone $special->getEnd())->modify("- 1 day");
        $calculation = $this->calc->calcPrices(
            $roomType,
            $tariff,
            $begin,
            $end,
            0,
            0,
            null,
            true,
            $special
        );

        $specialPrice = null;

        if ($calculation) {
            $specialPrice = new SpecialPrice();
            $specialPrice
                ->setTariff($tariff)
                ->setRoomType($roomType)
                ->setPrices($this->extractCalculationData($calculation));
        }

        return $specialPrice;
    }

    private function extractCalculationData(array $calculation): array
    {
        $result = [];
        foreach ($calculation as $calcKeys => $calcValue) {
            $result[$calcKeys] = $calcValue['total'];
        }

        return $result;
    }


    /**
     * @param array $specialIds
     * @return mixed
     */
    private function getSpecials(array $specialIds)
    {
        $qb = $this->dm->getRepository('MBHPriceBundle:Special')->createQueryBuilder();

        if (count($specialIds) == 0) {
            $qb->field('displayTo')->gte(new \DateTime('midnight - 10 days'));
        } else {
            $qb->field('id')->in($specialIds);
        }

        return $qb->getQuery()->execute();
    }
}