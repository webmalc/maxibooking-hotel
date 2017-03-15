<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;

class HomeAwayDataFormatter
{
    /** @var DocumentManager $dm */
    private $dm;
    /** @var RoomTypeManager $roomManager */
    private $roomManager;
    /** @var  SearchFactory $search */
    private $searchFactory;

    public function __construct(
        DocumentManager $dm,
        RoomTypeManager $manager,
        SearchFactory $searchFactory
    ) {
        $this->dm = $dm;
        $this->roomManager = $manager;
        $this->searchFactory = $searchFactory;
    }

    public function getSearchResults(
        $roomTypeId,
        $adultCount,
        $childrenCount,
        $beginString,
        $endString,
        Tariff $tariff
    ) {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = Helper::getDateFromString($beginString, 'Y-m-d');
        $query->end = Helper::getDateFromString($endString, 'Y-m-d');
        $query->addRoomType($roomTypeId);
        $query->adults = $adultCount;
        $query->children = $childrenCount;
        $query->tariff = $tariff;

        return $this->searchFactory->setWithTariffs()->search($query);
    }

    public function getPriceCaches($beginDate, $endDate, Hotel $hotel, $roomTypeId, $tariffId)
    {
        $requestedPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $beginDate,
            $endDate,
            $hotel,
            [$roomTypeId],
            [$tariffId],
            true,
            $this->roomManager->useCategories
        );

        return $requestedPriceCaches[$roomTypeId][$tariffId];
    }

    public function getRestrictions(
        $beginDate,
        $endDate,
        Hotel $hotel,
        $roomTypeId,
        $tariffId
    ) {
        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $beginDate,
            $endDate,
            $hotel,
            [$roomTypeId],
            [$tariffId],
            true
        );

        return isset($restrictions[$roomTypeId][$tariffId]) ? $restrictions[$roomTypeId][$tariffId] : [];
    }

    public function getRoomCaches($beginDate, $endDate, Hotel $hotel, $roomTypeId, $tariffId)
    {
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $beginDate,
            $endDate,
            $hotel,
            [$roomTypeId],
            false,
            true
        );

        return isset($roomCaches[$roomTypeId]) ? current($roomCaches[$roomTypeId]) : [];
    }

    public function getConfigs()
    {
        return $this->dm->getRepository('MBHChannelManagerBundle:HomeAwayConfig')->findBy(['isEnabled' => true]);
    }
}