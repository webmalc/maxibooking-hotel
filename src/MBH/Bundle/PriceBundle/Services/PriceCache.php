<?php

namespace MBH\Bundle\PriceBundle\Services;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    private $roomManager;

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
     * @throws \Exception
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
        $oldPriceCachesCallback = function () use ($begin, $end, $hotel, $roomTypes, $tariffs) {
            return $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch(
                    $begin, $end, $hotel, $this->helper->toIds($roomTypes), $this->helper->toIds($tariffs), false, $this->roomManager->useCategories
                );
        };
        $oldPriceCaches = $this->helper->getFilteredResult($this->dm, $oldPriceCachesCallback);

        /** @var \MBH\Bundle\PriceBundle\Document\PriceCache $oldPriceCache */
        foreach ($oldPriceCaches as $oldPriceCache) {

            if (!empty($weekdays) && !in_array($oldPriceCache->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldPriceCache->getDate()->format('d.m.Y')][$oldPriceCache->getTariff()->getId()][$oldPriceCache->getCategoryOrRoomType($this->roomManager->useCategories)->getId()] = $oldPriceCache;

            if ($oldPriceCache->getPrice() == $price
                && $oldPriceCache->getChildPrice() == $childPrice
                && $oldPriceCache->getIsPersonPrice() == $isPersonPrice
                && $oldPriceCache->getSinglePrice() == $singlePrice
                && $oldPriceCache->getAdditionalPrice() == $additionalPrice
                && $oldPriceCache->getAdditionalChildrenPrice() == $additionalChildrenPrice
                && $oldPriceCache->getAdditionalPrices() == $additionalPrices
                && $oldPriceCache->getAdditionalChildrenPrices() == $additionalChildrenPrices
            ) {
                continue;
            }

            $updates[] = [
                'criteria' => ['_id' => new \MongoId($oldPriceCache->getId())],
                'values' => [
                    'isEnabled' => false,
                    'cancelDate' => new \MongoDate((new \DateTime())->getTimestamp())
                ]
            ];

            if ($this->roomManager->useCategories) {
                $field = 'roomTypeCategory';
                $collection = 'RoomTypeCategory';
                $collectionId = $oldPriceCache->getRoomTypeCategory()->getId();
            } else {
                $field = 'roomType';
                $collection = 'RoomTypes';
                $collectionId = $oldPriceCache->getRoomType()->getId();
            }

            if ($price != -1) {
                $priceCaches[] = [
                    'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                    $field => \MongoDBRef::create($collection, new \MongoId($collectionId)),
                    'tariff' => \MongoDBRef::create('Tariffs', new \MongoId($oldPriceCache->getTariff()->getId())),
                    'createdAt' => new \MongoDate((new \DateTime())->getTimestamp()),
                    'date' => new \MongoDate($oldPriceCache->getDate()->getTimestamp()),
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

        if ($price != -1) {
            foreach ($tariffs as $tariff) {
                foreach ($roomTypes as $roomType) {
                    /** @var \DateTime $date */
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
                            'createdAt' => new \MongoDate((new \DateTime())->getTimestamp()),
                            'price' => (float)$price,
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
        }

        $this->container->get('mbh.mongo')->batchInsert('PriceCache', $priceCaches);
        $this->container->get('mbh.mongo')->update('PriceCache', $updates);
    }
}
