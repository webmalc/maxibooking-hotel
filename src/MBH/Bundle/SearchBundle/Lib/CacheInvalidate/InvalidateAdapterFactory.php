<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;

class InvalidateAdapterFactory
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
     * @return InvalidateAdapterInterface
     * @throws InvalidateException
     */
    public function createAdapter(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
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

        throw new InvalidateException('Can not found Adapter');

    }

    private function createPriceCache(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setTariffIds((array)$object->getTariff()->getId())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $adapter;
    }

    private function createRestrictions(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var PriceCache $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();

        $isUseCategory = $this->roomManager->useCategories;
        $roomTypeIds = [];
        if ($isUseCategory) {
            $category = $object->getRoomTypeCategory();
            $roomTypes = $category->getTypes();
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[] = $roomType->getId();
            }
        } else {
            $roomTypeIds = (array)$object->getRoomType();
        }
        $adapter
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setTariffIds((array)$object->getTariff()->getId())
            ->setRoomTypeIds($roomTypeIds);

        return $adapter;
    }

    private function createRoomCache(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var RoomCache $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $adapter;
    }

    private function createRoomType(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var RoomType $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter->setRoomTypeIds((array)$object->getId());

        return $adapter;
    }

    private function createTariff(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var Tariff $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter->setTariffIds((array)$object->getId());

        return $adapter;
    }

    private function createPackage(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var Package $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $adapter;
    }

    private function createPriceGenerator(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        $adapter = new InvalidateAdapter();

        $isUseCategory = $this->roomManager->useCategories;
        $roomTypeIds = [];
        if ($isUseCategory) {
            $categoryIds = $invalidateQuery->getCategoryIds();
            $roomCategories = $this->dm->getRepository(RoomTypeCategory::class)->findBy(['id' => $categoryIds]);
            foreach ($roomCategories as $roomCategory) {
                $roomTypes = $roomCategory->getTypes();
                foreach ($roomTypes as $roomType) {
                    $roomTypeIds[] = $roomType->getId();
                }
            }
        } else {
            $roomTypeIds = $invalidateQuery->getRoomTypeIds();
        }

        $adapter
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setTariffIds($invalidateQuery->getTariffIds())
            ->setRoomTypeIds($roomTypeIds);

        return $adapter;
    }

    private function createRestrictionGenerator(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        $adapter = new InvalidateAdapter();
        $adapter
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setRoomTypeIds($invalidateQuery->getRoomTypeIds())
            ->setTariffIds($invalidateQuery->getTariffIds());

        return $adapter;
    }

    private function createRoomCacheGenerator(InvalidateQuery $invalidateQuery): InvalidateAdapterInterface
    {
        /** @var RoomCache $object */
        $object = $invalidateQuery->getObject();
        $adapter = new InvalidateAdapter();
        $adapter
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $adapter;
    }

}