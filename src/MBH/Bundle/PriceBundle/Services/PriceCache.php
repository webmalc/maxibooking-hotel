<?php

namespace MBH\Bundle\PriceBundle\Services;


use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \MBH\Bundle\PriceBundle\Document\RoomCache as RoomCacheDoc;


/**
 *  PriceCache service
 */
class PriceCache
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->helper = $this->container->get('mbh.helper');
        $this->roomManager = $this->container->get('mbh.hotel.room_type_manager');
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param $price
     * @param bool|false $isPersonPrice
     * @param null $singlePrice
     * @param null $additionalPrice
     * @param null $additionalChildrenPrice
     * @param array $availableRoomTypes
     * @param array $availableTariffs
     * @param array $weekdays
     * @param null $childPrice
     * @param array $additionalPrices
     * @param array $additionalChildrenPrices
     */
    public function update(
        \DateTime $begin,
        \DateTime $end,
        Hotel $hotel,
        $price,
        $isPersonPrice = false,
        $singlePrice = null,
        $additionalPrice = null,
        $additionalChildrenPrice = null,
        array $availableRoomTypes = [],
        array $availableTariffs = [],
        array $weekdays = [],
        $childPrice = null,
        array $additionalPrices = [],
        array $additionalChildrenPrices = []
    ) {
        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');
        $priceCaches = $updateCaches = $updates = $remove = [];

        is_numeric($singlePrice) ? $singlePrice = (float) $singlePrice : $singlePrice;
        is_numeric($additionalPrice) ? $additionalPrice = (float) $additionalPrice : $additionalPrice;
        is_numeric($childPrice) ? $childPrice = (float) $childPrice : $childPrice;
        is_numeric($additionalChildrenPrice) ? $additionalChildrenPrice = (float) $additionalChildrenPrice : $additionalChildrenPrice;

        foreach ($additionalPrices as $key => $p) {
            if ($p != '' && !is_null($p)) {
                $additionalPrices[$key] = (float) $p;
            } else {
                $additionalPrices[$key] = null;
            }
        }
        foreach ($additionalChildrenPrices as $key => $p) {
            if ($p != '' && !is_null($p)) {
                $additionalChildrenPrices[$key] = (float) $p;
            } else {
                $additionalChildrenPrices[$key] = null;
            }
        }

        $roomTypes = $availableRoomTypes;
        if (empty($roomTypes)) {
            $roomTypes = $this->roomManager->getRooms($hotel)->toArray();
        }

        (empty($availableTariffs)) ? $tariffs = $hotel->getTariffs()->toArray() : $tariffs = $availableTariffs;

        // find && group old caches
        $oldPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch(
                $begin, $end, $hotel, $this->helper->toIds($roomTypes), $this->helper->toIds($tariffs), false, $this->roomManager->useCategories
            );

        foreach ($oldPriceCaches as $oldPriceCache) {

            if (!empty($weekdays) && !in_array($oldPriceCache->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldPriceCache->getDate()->format('d.m.Y')][$oldPriceCache->getTariff()->getId()][$oldPriceCache->getCategoryOrRoomType($this->roomManager->useCategories)->getId()] = $oldPriceCache;

            if ($price == -1) {
                $remove['_id']['$in'][] = new \MongoId($oldPriceCache->getId());
            }

            $updates[] = [
                'criteria' => ['_id' => new \MongoId($oldPriceCache->getId())],
                'values' => [
                    'price' => (float) $price,
                    'childPrice' => $childPrice,
                    'isPersonPrice' => $isPersonPrice,
                    'singlePrice' => $singlePrice,
                    'additionalPrice' => $additionalPrice,
                    'additionalChildrenPrice' => $additionalChildrenPrice,
                    'additionalPrices' => $additionalPrices,
                    'additionalChildrenPrices' => $additionalChildrenPrices
                ]
            ];
        }
        foreach ($tariffs as $tariff) {
            foreach ($roomTypes as $roomType) {
                foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $endWithDay) as $date) {

                    if (isset($updateCaches[$date->format('d.m.Y')][$tariff->getId()][$roomType->getId()])) {
                        continue;
                    }
                    if (!empty($weekdays) && !in_array($date->format('w'), $weekdays)) {
                        continue;
                    }

                    if ($this->roomManager->useCategories) {
                        $field = 'roomTypeCategory';
                        $collection = 'RoomTypeCategory';
                    } else {
                        $field = 'roomType';
                        $collection = 'RoomTypes';
                    }

                    $priceCaches[] = [
                        'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                        $field => \MongoDBRef::create($collection, new \MongoId($roomType->getId())),
                        'tariff' => \MongoDBRef::create('Tariffs', new \MongoId($tariff->getId())),
                        'date' => new \MongoDate($date->getTimestamp()),
                        'price' => (float) $price,
                        'childPrice' => $childPrice,
                        'isPersonPrice' => $isPersonPrice,
                        'singlePrice' => $singlePrice,
                        'additionalPrice' => $additionalPrice,
                        'additionalChildrenPrice' => $additionalChildrenPrice,
                        'additionalPrices' => $additionalPrices,
                        'additionalChildrenPrices' => $additionalChildrenPrices,
                        'isEnabled' => true
                    ];
                }
            }
        }

        if ($price == -1) {
            $this->container->get('mbh.mongo')->remove('PriceCache', $remove);
        } else {
            $this->container->get('mbh.mongo')->batchInsert('PriceCache', $priceCaches);
            $this->container->get('mbh.mongo')->update('PriceCache', $updates);
        }
    }
}
