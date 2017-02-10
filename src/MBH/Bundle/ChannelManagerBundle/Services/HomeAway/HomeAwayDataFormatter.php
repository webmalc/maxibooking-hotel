<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
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
        RoomTypeManager $manager
    ) {
        $this->dm = $dm;
        $this->roomManager = $manager;
    }

    public function getHomeAwayConfigByRoomId($homeAwayRoomTypeId)
    {
        return $this->dm->getRepository('MBHChannelManagerBundle:HomeAwayConfig')
            ->findOneBy(['rooms.roomId' => $homeAwayRoomTypeId]);
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

    public function getPriceCaches($beginDate, $endDate, HomeAwayConfig $config, $roomTypeId, $tariffId)
    {
        $requestedPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
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
        ChannelManagerConfigInterface $config,
        $roomTypeId,
        $tariffId
    ) {
        return $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
            [$roomTypeId],
            [$tariffId],
            true
        );
    }

    public function getRoomCaches($beginDate, $endDate, ChannelManagerConfigInterface $config, $roomTypeId, $tariffId)
    {
        return $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $beginDate,
            $endDate,
            $config->getHotel(),
            [$roomTypeId],
            [$tariffId],
            true
        );
    }
}