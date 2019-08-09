<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;

class InvalidateMessageFactory
{

    /** @var RoomTypeManager */
    private $roomManager;

    /** @var DocumentManager */
    private $dm;

    /**
     * InvalidateAdapterFactory constructor.
     * @param RoomTypeManager $roomManager
     * @param DocumentManager $dm
     */
    public function __construct(RoomTypeManager $roomManager, DocumentManager $dm)
    {
        $this->roomManager = $roomManager;
        $this->dm = $dm;
    }


    /**
     * @param InvalidateQuery $invalidateQuery
     * @return InvalidateMessageInterface
     * @throws InvalidateException
     */
    public function createMessage(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $type = $invalidateQuery->getType();
        if (InvalidateQuery::PRICE_CACHE === $type) {
            return $this->createPriceCache($invalidateQuery);
        }
        if (InvalidateQuery::RESTRICTIONS === $type) {
            return $this->createRestrictions($invalidateQuery);
        }

        if (InvalidateQuery::ROOM_CACHE === $type) {
            return $this->createRoomCache($invalidateQuery);
        }

        if (InvalidateQuery::ROOM_TYPE === $type) {
            return $this->createRoomType($invalidateQuery);
        }

        if (InvalidateQuery::TARIFF === $type) {
            return $this->createTariff($invalidateQuery);
        }

        if (InvalidateQuery::PACKAGE === $type) {
            return $this->createPackage($invalidateQuery);
        }

        if (InvalidateQuery::PRICE_GENERATOR === $type) {
            return $this->createPriceGenerator($invalidateQuery);
        }

        if (InvalidateQuery::RESTRICTION_GENERATOR === $type) {
            return $this->createRestrictionGenerator($invalidateQuery);
        }

        if (InvalidateQuery::ROOM_CACHE_GENERATOR === $type) {
            return $this->createRoomCacheGenerator($invalidateQuery);
        }

        throw new InvalidateException('Adapter is not found.');

    }

    private function createPriceCache(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var PriceCache $priceCache */
        $priceCache = $invalidateQuery->getObject();
        $message = new InvalidateMessage();

        $isUseCategory = $this->roomManager->getIsUseCategories();
        $roomTypeIds = [];
        if ($isUseCategory) {
            $category = $priceCache->getRoomTypeCategory();
            $roomTypes = $category->getTypes();
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[] = $roomType->getId();
            }
        } else {
            $roomTypeIds = (array)$priceCache->getRoomType()->getId();
        }

        $priceCacheTariff = $priceCache->getTariff();

        $tariffIds = array_merge((array)$priceCacheTariff->getId(), $this->getChildrenTariffIds($priceCacheTariff, 'prices'));
        $message
            ->setBegin(clone $priceCache->getDate())
            ->setEnd(clone $priceCache->getDate())
            ->setTariffIds($tariffIds)
            ->setRoomTypeIds($roomTypeIds);

        return $message;
    }

    private function createRestrictions(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var Restriction $restriction */
        $restriction = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $tariff = $restriction->getTariff();
        $tariffIds = array_merge((array)$tariff->getId(), $this->getChildrenTariffIds($tariff, 'restrictions'));
        $message
            ->setBegin(clone $restriction->getDate())
            ->setEnd(clone $restriction->getDate())
            ->setTariffIds($tariffIds)
            ->setRoomTypeIds((array)$restriction->getRoomType()->getId());

        return $message;
    }

    private function createRoomCache(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var RoomCache $roomCache */
        $roomCache = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message
            ->setBegin(clone $roomCache->getDate())
            ->setEnd(clone $roomCache->getDate())
            ->setRoomTypeIds((array)$roomCache->getRoomType()->getId());

        return $message;
    }

    private function createRoomType(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var RoomType $roomType */
        $roomType = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message->setRoomTypeIds((array)$roomType->getId());

        return $message;
    }

    private function createTariff(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var Tariff $tariff */
        $tariff = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $childrenTariffs = $tariff->getChildren();
        $childrenTariffIds = [];
        if (null !== $childrenTariffs) {
            $childrenTariffIds = Helper::toIds($childrenTariffs);
        }
        $tariffIds = array_merge((array)$tariff->getId(), $childrenTariffIds);
        $message->setTariffIds($tariffIds);

        return $message;
    }

    private function createPackage(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var Package $package */
        $package = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message
            ->setRoomTypeIds((array)$package->getRoomType()->getId())
            ->setBegin($package->getBegin())
            ->setEnd($package->getEnd())
        ;

        return $message;
    }

    private function createPriceGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $message = new InvalidateMessage();

        $isUseCategory = $this->roomManager->getIsUseCategories();
        $roomTypeIds = [];
        if ($isUseCategory) {
            $categoryIds = $invalidateQuery->getRoomTypeIds();
            $roomCategories = $this->dm->getRepository(RoomTypeCategory::class)->findBy(['id' => ['$in' => $categoryIds]]);
            foreach ($roomCategories as $roomCategory) {
                $roomTypes = $roomCategory->getTypes();
                foreach ($roomTypes as $roomType) {
                    $roomTypeIds[] = $roomType->getId();
                }
            }
        } else {
            $roomTypeIds = $invalidateQuery->getRoomTypeIds();
        }

        $priceGeneratorTariffIds = $invalidateQuery->getTariffIds();
        $tariffIds = $this->getMainAndChildrenTariffIdsFromArray($priceGeneratorTariffIds, 'prices');

        $message
            ->setBegin(clone $invalidateQuery->getBegin())
            ->setEnd(clone $invalidateQuery->getEnd())
            ->setTariffIds($tariffIds)
            ->setRoomTypeIds($roomTypeIds);

        return $message;
    }

    private function createRestrictionGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $message = new InvalidateMessage();
        $tariffIds = $this->getMainAndChildrenTariffIdsFromArray($invalidateQuery->getTariffIds(), 'prices');
        $message
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setRoomTypeIds($invalidateQuery->getRoomTypeIds())
            ->setTariffIds($tariffIds);

        return $message;
    }

    private function createRoomCacheGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $message = new InvalidateMessage();
        $message
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setRoomTypeIds($invalidateQuery->getRoomTypeIds());

        return $message;
    }

    private function getMainAndChildrenTariffIdsFromArray(array $tariffIds, string $inheritanceType): array
    {
        $tariffs = $this->dm->getRepository(Tariff::class)->findBy(['id' => ['$in' => $tariffIds]]);
        $tariffIds = [];
        if (is_iterable($tariffs)) {
            foreach ($tariffs as $tariff) {
                $tariffIds[] = (array)$tariff->getId();
                $tariffIds[] = $this->getChildrenTariffIds($tariff, $inheritanceType);
            }
        }

        if(!empty($tariffIds)) {
            $tariffIds = array_merge(...$tariffIds);
        }

        return $tariffIds;
    }

    private function getChildrenTariffIds(Tariff $tariff, string $inheritanceType): array
    {
        $types = [
            'rooms' => 'isInheritRooms', 'restrictions' => 'isInheritRestrictions', 'prices' => 'isInheritPrices'
        ];

        $childrenTariffIds = [];
        if (null !== $childrenTariffs = $tariff->getChildren()) {

            if (count($tariffs = $childrenTariffs->toArray())) {
                $method = $types[$inheritanceType];
                $actualChildrenTariffs = array_filter($tariffs, function (Tariff $tariff) use ($method) {
                    return $tariff->getChildOptions()->$method();
                });
                $childrenTariffIds = Helper::toIds($actualChildrenTariffs);
            }

        }

        return $childrenTariffIds;
    }


}