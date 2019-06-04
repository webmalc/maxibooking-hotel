<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;

class CalculationHelper
{
    /** @var RoomTypeManager */
    private $roomTypeManager;

    /**
     * CalculationHelper constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager)
    {
        $this->roomTypeManager = $roomTypeManager;
    }

    public function isIndividualAdditionalPrices(RoomType $roomType): bool
    {
        if ($this->roomTypeManager->useCategories) {
            if (null === $roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $roomType->getCategory()->getIsIndividualAdditionalPrices();
        }

        return $roomType->getIsIndividualAdditionalPrices();
    }

    public function isChildPrices(RoomType $roomType): bool
    {
        if ($this->roomTypeManager->useCategories) {
            if (null === $roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $roomType->getCategory()->getIsChildPrices();
        }

        return $roomType->getIsChildPrices();
    }


    public function getCombinations(int $adults, int $children, RoomType $roomType): array
    {
        $result = [];
        if ($adults === 0 && $children === 0) {
            $this->roomTypeManager->useCategories ? $isChildPrices = $roomType->getCategory()->getIsChildPrices() : $isChildPrices = $roomType->getIsChildPrices();
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