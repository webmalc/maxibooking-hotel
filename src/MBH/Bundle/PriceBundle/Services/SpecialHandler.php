<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
        $this->search = $search->setWithTariffs();
    }

    /**
     * @param Special[] $specials
     * @param RoomType[] $roomTypes
     */
    public function calculatePrices(array $specials = [], array $roomTypes = [])
    {
        $searchQuery = new SearchQuery();
        $searchQuery->isOnline = true;

        is_null($roomTypes) ?: $searchQuery->roomTypes = $roomTypes;
        if (count($specials) == 0) {
            $searchQuery->begin = new \DateTime('midnight');
            $searchQuery->end = new \DateTime('midnight + 2 year');
            $specials = $this->search->searchSpecials($searchQuery)->toArray();
        }
        $currentDate = new \DateTime('midnight');

        /** @var Special $special */
        foreach ($specials as $special) {
            $special->removeAllPrices();
            if ($special->getIsEnabled()
                && $special->getRemain() > 0
                && $special->getBegin() > $currentDate
                && $special->getDisplayFrom() < $currentDate
                && $special->getDisplayTo() > $currentDate
            ) {
                $searchQuery->setSpecial($special);
                $searchQuery->begin = $special->getBegin();
                $searchQuery->end = $special->getEnd();
                $results = $this->search->search($searchQuery);

                foreach ($results as $resultsByRoomType) {
                    /** @var RoomType $roomType */
                    $roomType = $resultsByRoomType['roomType'];
                    if (in_array($roomType, $special->getRoomTypes()->toArray())) {

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
                }
            }
        }

        $this->dm->flush();
    }
}