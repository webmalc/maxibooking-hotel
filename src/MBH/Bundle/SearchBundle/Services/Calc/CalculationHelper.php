<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcherInterface;

class CalculationHelper
{
    /** @var RoomTypeManager */
    private $roomTypeManager;

    /** @var SharedDataFetcherInterface */
    private $sharedDataFetcher;

    /**
     * CalculationHelper constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager, SharedDataFetcherInterface $sharedDataFetcher)
    {
        $this->roomTypeManager = $roomTypeManager;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }

    public function isIndividualAdditionalPrices(RoomType $roomType): bool
    {
        if ($this->roomTypeManager->getIsUseCategories()) {
            if (null === $roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $roomType->getCategory()->getIsIndividualAdditionalPrices();
        }

        return $roomType->getIsIndividualAdditionalPrices();
    }

    public function isChildPrices(RoomType $roomType): bool
    {
        if ($this->roomTypeManager->getIsUseCategories()) {
            if (null === $roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $roomType->getCategory()->getIsChildPrices();
        }

        return $roomType->getIsChildPrices();
    }


    public function getCombinations(int $adults, int $children, string $roomTypeId): array
    {
        $roomType = $this->sharedDataFetcher->getFetchedRoomType($roomTypeId);
        $result = [];
        if ($adults === 0 && $children === 0) {
            $this->roomTypeManager->getIsUseCategories() ? $isChildPrices = $roomType->getCategory()->getIsChildPrices() : $isChildPrices = $roomType->getIsChildPrices();
            $total = $roomType->getTotalPlaces();

            $isChildPrices ? $additional = $roomType->getTotalPlaces() : $additional = $roomType->getAdditionalPlaces();
            $isChildPrices ? $places = 1 : $places = $roomType->getPlaces();

            for ($i = 1; $i <= $total; $i++) {
                $result[] = ['adults' => $i, 'children' => 0];
            }
            for ($i = $places; $i <= $total; $i++) {
                for ($k = 1; $k <= $additional; $k++) {
                    if (($k + $i) && ($k + $i) <= $total) {
                        $result[] = ['adults' => $i, 'children' => $k];
                    }
                }
            }
        } else {
            $result = [0 => ['adults' => $adults, 'children' => $children]];
        }

        return $result;
    }

}