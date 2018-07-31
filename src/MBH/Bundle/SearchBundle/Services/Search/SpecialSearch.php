<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\SpecialRepository;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use MBH\Bundle\SearchBundle\Document\SearchConditions;

class SpecialSearch
{

    /** @var SpecialRepository */
    private $specialRepository;

    /** @var RoomTypeManager */
    private $roomManager;

    /** @var RoomTypeCategoryRepository */
    private $roomTypeCategoryRepository;

    /**
     * SpecialSearch constructor.
     * @param SpecialRepository $specialRepository
     * @param RoomTypeManager $roomManager
     * @param RoomTypeCategoryRepository $roomTypeCategoryRepository
     */
    public function __construct(
        SpecialRepository $specialRepository,
        RoomTypeManager $roomManager,
        RoomTypeCategoryRepository $roomTypeCategoryRepository
    ) {
        $this->specialRepository = $specialRepository;
        $this->roomManager = $roomManager;
        $this->roomTypeCategoryRepository = $roomTypeCategoryRepository;
    }


    public function search(SearchConditions $conditions): array
    {
        $specialFilter = $this->createSpecialFilter($conditions);

        return $this->specialRepository->getStrictBeginFiltered($specialFilter)->toArray();
    }

    private function createSpecialFilter(SearchConditions $conditions): SpecialFilter
    {
        $filter = new SpecialFilter();
        $filter
            ->setRemain(1)
            ->setDisplayFrom($conditions->getBegin())
            ->setDisplayTo($conditions->getEnd());

        if ($conditions->isSpecialStrict() && $conditions->getBegin() && $conditions->getEnd()) {
            $filter->setBegin($conditions->getBegin());
            $filter->setEnd($conditions->getEnd());
            $filter->setIsStrict(true);
        }
        if ($adults = $conditions->getAdults()) {
            $filter->setAdults($adults);
        }
        if ($children = $conditions->getChildren()) {
            $filter->setChildren($children);
        }
        if ($childrenAges = $conditions->getChildrenAges()) {
            $filter->setChildrenAges($childrenAges);
        }

        if ($roomTypes = $conditions->getRoomTypes()) {
            if ($this->roomManager->useCategories) {
                foreach ($roomTypes as $roomTypeCategory) {
                    /** @var RoomTypeCategory $roomTypeCategory */
                    $roomTypes = $roomTypeCategory->getTypes()->toArray();
                    foreach ($roomTypes as $roomType) {
                        $filter->addRoomType($roomType);
                    }
                }
            } else {
                $filter->setRoomTypes($roomTypes->toArray());
            }

        }

        return $filter;
    }
}