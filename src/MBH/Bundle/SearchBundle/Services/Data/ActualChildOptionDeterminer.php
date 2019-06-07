<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Exceptions\DataManagerException;

class ActualChildOptionDeterminer
{
    /** @var string  */
    private const RESTRICTION_FIELD = 'isInheritRestrictions';

    private const PRICE_FIELD = 'isInheritPrices';

    private const ROOM_FIELD = 'isInheritRooms';

    /** @var SharedDataFetcher */
    private $sharedData;

    /**
     * ActualChildOptionDeterminer constructor.
     * @param SharedDataFetcher $sharedData
     */
    public function __construct(SharedDataFetcher $sharedData)
    {
        $this->sharedData = $sharedData;
    }

    public function getActualRestrictionTariff(string $tariffId): string
    {
        return $this->getActualField($tariffId, self::RESTRICTION_FIELD);
    }

    public function getActualPriceTariff(string $tariffId): string
    {
        return $this->getActualField($tariffId, self::PRICE_FIELD);
    }

    public function getActualRoomTariff(string $tariffId): string
    {
        return $this->getActualField($tariffId, self::ROOM_FIELD);
    }

    public function getActualCategoryId(string $roomTypeId): string
    {
        $category = $this->sharedData->getFetchedRoomType($roomTypeId)->getCategory();
        if (!$category) {
            throw new DataManagerException(sprintf('Try to determine category, but actually no category! in %s', __CLASS__));
        }

        return $category->getId();
    }

    private function getActualField(string $tariffId, string $fieldName): string
    {
        $tariff = $this->sharedData->getFetchedTariff($tariffId);

        if ($tariff->getParent() && $tariff->getChildOptions()->$fieldName()) {
            $tariffId = $this->getActualField($tariff->getParent()->getId(), $fieldName);
        }

        return $tariffId;
    }


}