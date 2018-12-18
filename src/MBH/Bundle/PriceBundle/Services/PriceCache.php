<?php

namespace MBH\Bundle\PriceBundle\Services;

use MBH\Bundle\PriceBundle\Lib\PriceCacheHolderDataGeneratorForm;
use MBH\Bundle\PriceBundle\Lib\PriceCacheSkippingDate;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->helper = $this->container->get('mbh.helper');
        $this->roomManager = $this->container->get('mbh.hotel.room_type_manager');
        $this->resultUpdate = $this->container->get('mbh.price.cache.result_update');
    }

    /**
     * @param PriceCacheHolderDataGeneratorForm $holderDataForm
     * @return PriceCacheResultUpdate
     * @throws \MongoException
     */
    public function update(PriceCacheHolderDataGeneratorForm $holderDataForm): PriceCacheResultUpdate
    {
        $priceCachesUpdate = $priceCachesCreate = $updateCaches = $updates = $remove = [];;

        $begin = $holderDataForm->getBegin();
        $end = $holderDataForm->getEnd();
        $hotel = $holderDataForm->getHotel();
        $price = $holderDataForm->getPrice();
        $isPersonPrice = $holderDataForm->isPersonPrice();
        $singlePrice = $holderDataForm->getSinglePrice();
        $additionalPrice = $holderDataForm->getAdditionalPrice();
        $additionalChildrenPrice = $holderDataForm->getAdditionalChildrenPrice();
        $availableRoomTypes = $holderDataForm->getRoomTypesAsArray();
        $availableTariffs = $holderDataForm->getTariffsAsArray();
        $weekdays = $holderDataForm->getWeekdays();
        $childPrice = $holderDataForm->getChildPrice();
        $additionalPrices = $holderDataForm->getAdditionalPrices();
        $additionalChildrenPrices = $holderDataForm->getAdditionalChildrenPrices();

        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');

        $roomTypes = $availableRoomTypes;
        if (empty($roomTypes)) {
            $roomTypes = $this->roomManager->getRooms($hotel)->toArray();
        }

        $availableTariffs === [] ? $tariffs = $hotel->getTariffs()->toArray() : $tariffs = $availableTariffs;

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

            if ($weekdays !== [] && !in_array($oldPriceCache->getDate()->format('w'), $weekdays)) {
                $this
                    ->resultUpdate
                    ->addSkippedDaysAtUpdate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_WEEKDAYS,$oldPriceCache->getDate()));
                continue;
            }

            $updateCaches[$oldPriceCache->getDate()->format('d.m.Y')][$oldPriceCache->getTariff()->getId()][$oldPriceCache->getCategoryOrRoomType($this->roomManager->useCategories)->getId()] = $oldPriceCache;

            $tempPriceCache = $holderDataForm->createPriceCache();

            if ($oldPriceCache->isSamePriceCaches($tempPriceCache)) {
                $this
                    ->resultUpdate
                    ->addSkippedDaysAtUpdate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_SAME, $oldPriceCache->getDate()));
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
                $priceCachesUpdate[] = [
                    'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                    $field => \MongoDBRef::create($collection, new \MongoId($collectionId)),
                    'tariff' => \MongoDBRef::create('Tariffs', new \MongoId($oldPriceCache->getTariff()->getId())),
                    'createdAt' => new \MongoDate((new \DateTime())->getTimestamp()),
                    'date' => new \MongoDate($oldPriceCache->getDate()->getTimestamp()),
                    'price' => $price,
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

        $this->resultUpdate->setAmountRemove(count($updates));
        $this->resultUpdate->setAmountUpdate(count($priceCachesUpdate));

        if ($price != -1) {
            foreach ($tariffs as $tariff) {
                foreach ($roomTypes as $roomType) {
                    /** @var \DateTime $date */
                    foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $endWithDay) as $date) {

                        if (isset($updateCaches[$date->format('d.m.Y')][$tariff->getId()][$roomType->getId()])) {
                            continue;
                        }
                        if ($weekdays !== [] && !in_array($date->format('w'), $weekdays)) {
                            $this
                                ->resultUpdate
                                ->addSkippedDaysAtCreate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_WEEKDAYS, $date));
                            continue;
                        }

                        if ($this->roomManager->useCategories) {
                            $field = 'roomTypeCategory';
                            $collection = 'RoomTypeCategory';
                        } else {
                            $field = 'roomType';
                            $collection = 'RoomTypes';
                        }

                        $priceCachesCreate[] = [
                            'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                            $field => \MongoDBRef::create($collection, new \MongoId($roomType->getId())),
                            'tariff' => \MongoDBRef::create('Tariffs', new \MongoId($tariff->getId())),
                            'date' => new \MongoDate($date->getTimestamp()),
                            'createdAt' => new \MongoDate((new \DateTime())->getTimestamp()),
                            'price' => $price,
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

        $this->resultUpdate->setAmountCreate(count($priceCachesCreate));

        $this->container->get('mbh.mongo')
            ->batchInsert('PriceCache', array_merge($priceCachesUpdate, $priceCachesCreate));
        $this->container->get('mbh.mongo')->update('PriceCache', $updates);

        return $this->resultUpdate;
    }
}
