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

        throw new InvalidateException('Can not found Adapter');

    }

    private function createPriceCache(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var PriceCache $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();

        $isUseCategory = $this->roomManager->useCategories;
        $roomTypeIds = [];
        if ($isUseCategory) {
            $category = $object->getRoomTypeCategory();
            $roomTypes = $category->getTypes();
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[] = $roomType->getId();
            }
        } else {
            $roomTypeIds = (array)$object->getRoomType()->getId();
        }
        $message
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setTariffIds((array)$object->getTariff()->getId())
            ->setRoomTypeIds($roomTypeIds);

        return $message;
    }

    private function createRestrictions(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setTariffIds((array)$object->getTariff()->getId())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $message;
    }

    private function createRoomCache(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var RoomCache $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $message;
    }

    private function createRoomType(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var RoomType $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message->setRoomTypeIds((array)$object->getId());

        return $message;
    }

    private function createTariff(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var Tariff $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message->setTariffIds((array)$object->getId());

        return $message;
    }

    private function createPackage(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var Package $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $message;
    }

    private function createPriceGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $message = new InvalidateMessage();

        $isUseCategory = $this->roomManager->useCategories;
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

        $message
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setTariffIds($invalidateQuery->getTariffIds())
            ->setRoomTypeIds($roomTypeIds);

        return $message;
    }

    private function createRestrictionGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        $message = new InvalidateMessage();
        $message
            ->setBegin($invalidateQuery->getBegin())
            ->setEnd($invalidateQuery->getEnd())
            ->setRoomTypeIds($invalidateQuery->getRoomTypeIds())
            ->setTariffIds($invalidateQuery->getTariffIds());

        return $message;
    }

    private function createRoomCacheGenerator(InvalidateQuery $invalidateQuery): InvalidateMessageInterface
    {
        /** @var RoomCache $object */
        $object = $invalidateQuery->getObject();
        $message = new InvalidateMessage();
        $message
            ->setBegin($object->getDate())
            ->setEnd($object->getDate())
            ->setRoomTypeIds((array)$object->getRoomType()->getId());

        return $message;
    }

}