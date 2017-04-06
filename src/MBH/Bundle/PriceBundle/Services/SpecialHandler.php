<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
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
     * SpecialHandler constructor.
     * @param SearchFactory $search
     * @param DocumentManager $dm
     */
    public function __construct(SearchFactory $search, DocumentManager $dm)
    {
        $this->dm = $dm;
        $this->search = $search;
    }

    /**
     * @param array $specialIds
     * @param array $roomTypeIds
     */
    public function calculatePrices(array $specialIds = [], array $roomTypeIds = [])
    {
        $searchQuery = new SearchQuery();

        count($roomTypeIds) == 0 ?: $searchQuery->roomTypes = $specialIds;
        $specials = $this->getSpecials($specialIds);

        $currentDate = new \DateTime('midnight');
        $searchQuery->isOnline = true;
        /** @var Special $special */
        foreach ($specials as $special) {
            $special->removeAllPrices();
            if ($special->getIsEnabled()
                && $special->getRemain() > 0
                && $special->getBegin() > $currentDate
                && $special->getDisplayFrom() <= $currentDate
                && $special->getDisplayTo() >= $currentDate
            ) {
                $searchQuery->setSpecial($special);
                $searchQuery->begin = $special->getBegin();
                $searchQuery->end = $special->getEnd();
                $results = $this->search->search($searchQuery);

                foreach ($results as $resultsByRoomType) {
                    //$roomCategory может быть типом комнат или категорией типов комнат
                    $roomCategory = $resultsByRoomType['roomType'];
                    if ($roomCategory instanceof RoomTypeCategory) {
                        foreach ($roomCategory->getTypes() as $roomType) {
                            $this->calculateRoomTypeSpecialPrices($roomType, $roomTypes, $special, $resultsByRoomType);
                        }
                    } else {
                        $this->calculateRoomTypeSpecialPrices($roomCategory, $roomTypes, $special, $resultsByRoomType);
                    }
                }
            }
        }

        $this->dm->flush();
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

    /**
     * @param RoomType $roomType
     * @param $roomTypes
     * @param Special $special
     * @param $resultsByRoomType
     */
    private function calculateRoomTypeSpecialPrices(RoomType $roomType, $roomTypes, Special $special, $resultsByRoomType)
    {
        if (count($roomTypes) == 0 || in_array($roomType, $special->getRoomTypes()->toArray())) {

            /** @var SearchResult $resultByTariff */
            foreach ($resultsByRoomType['results'] as $resultByTariff) {

                /** @var Tariff $tariff */
                $tariff = $resultByTariff->getTariff();
                $specialTariffs = $special->getTariffs()->toArray();
                if (count($specialTariffs) == 0 || in_array($tariff, $special->getTariffs()->toArray())) {

                    foreach ($resultByTariff->getPrices() as $combination => $price) {
                        $underscorePosition = strpos($combination, '_');
                        $adultsCount = intval(substr($combination, 0, $underscorePosition));
                        $childrenCount = intval(substr($combination, $underscorePosition + 1));

                        $specialPrice = (new SpecialPrice())
                            ->setAdultsCount($adultsCount)
                            ->setChildrenCount($childrenCount)
                            ->setPrice($price)
                            ->setTariff($resultByTariff->getTariff())
                            ->setRoomType($roomType);

                        $special->addPrice($specialPrice);
                    }
                }
            }
        }

        $this->dm->flush();
    }
}