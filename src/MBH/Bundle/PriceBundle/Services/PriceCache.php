<?php

namespace MBH\Bundle\PriceBundle\Services;


use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \MBH\Bundle\PriceBundle\Document\RoomCache as RoomCacheDoc;


/**
 *  RoomCache service
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
    }


    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param int $price
     * @param boolean $isPersonPrice
     * @param int $singlePrice
     * @param int $additionalPrice
     * @param int $additionalChildrenPrice
     * @param array $availableRoomTypes
     * @param array $availableTariffs
     * @param array $weekdays
     */
    public function update(
        \DateTime $begin,
        \DateTime $end,
        Hotel $hotel,
        $price,
        $isPersonPrice = false,
        $singlePrice = null,
        $additional79Price = null,
        $additionalChildrenPrice = null,
        array $availableRoomTypes = [],
        array $availableTariffs = [],
        array $weekdays = []
    ) {
        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');
        $priceCaches = $updateCaches = $updates = [];

        (empty($availableRoomTypes)) ? $roomTypes = $hotel->getRoomTypes()->toArray() : $roomTypes = $availableRoomTypes;
        (empty($availableTariffs)) ? $tariffs = $hotel->getTariffs()->toArray() : $tariffs = $availableTariffs;

        // find && group old caches
        $oldPriceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')
            ->fetch($begin, $end, $hotel, $this->helper->toIds($roomTypes), $this->helper->toIds($tariffs));

        foreach ($oldPriceCaches as $oldPriceCache) {

            if (!empty($weekdays) && !in_array($oldPriceCache->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldPriceCache->getDate()->format('d.m.Y')][$oldPriceCache->getTariff()->getId()][$oldPriceCache->getRoomType()->getId()] = $oldPriceCache;

            // TODO: save prices as int & boolean personprice
            /*$updates[] = [
                'criteria' => ['_id' => new \MongoId($oldRoomCache->getId())],
                'values' => [
                    'packagesCount' => $oldRoomCache->getPackagesCount(),
                    'totalRooms' => (int) $rooms,
                    'leftRooms' => (int)$rooms - $oldRoomCache->getPackagesCount()
                ]
            ];*/
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

                    // TODO: save prices as int & boolean personprice
                    /*$roomCaches[] = [
                        'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                        'roomType' => \MongoDBRef::create('RoomTypes', new \MongoId($roomType->getId())),
                        'date' => new \MongoDate($date->getTimestamp()),
                        'totalRooms' => (int)$rooms,
                        'packagesCount' => (int)0,
                        'leftRooms' => (int)0
                    ];*/

                }
            }
        }

        $this->container->get('mbh.mongo')->batchInsert('PriceCache', $priceCaches);
        $this->container->get('mbh.mongo')->update('PriceCache', $updates);
    }
}
