<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\PromotionRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;

class InfoService
{

    /** @var RoomTypeRepository */
    private $roomTypeRepository;

    /** @var TariffRepository */
    private $tariffRepository;

    /** @var HotelRepository */
    private $hotelRepository;

    /** @var RoomTypeCategoryRepository */
    private $categoryRepository;

    /** @var array */
    private $data;
    /**
     * @var DocumentRepository
     */
    private $promotionRepository;

    /**
     * InfoService constructor.
     * @param RoomTypeRepository $roomTypeRepository
     * @param TariffRepository $tariffRepository
     * @param HotelRepository $hotelRepository
     * @param RoomTypeCategoryRepository $categoryRepository
     * @param PromotionRepository $promotionRepository
     */
    public function __construct(RoomTypeRepository $roomTypeRepository, TariffRepository $tariffRepository, HotelRepository $hotelRepository, RoomTypeCategoryRepository $categoryRepository, PromotionRepository $promotionRepository)
    {
        $this->roomTypeRepository = $roomTypeRepository;
        $this->tariffRepository = $tariffRepository;
        $this->hotelRepository = $hotelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->promotionRepository = $promotionRepository;
    }


    public function getInfo(): array
    {
        $this->initData();

        return [
            'roomTypes' => $this->getRoomTypesInfo(),
            'tariffs' => $this->getTariffsInfo(),
            'hotels' => $this->getHotelsInfo(),
            'categories' => $this->getRoomTypeCategoriesInfo(),
            'images' => $this->getImagesInfo(),
            'promotions' => $this->getPromotions()
        ];
    }

    private function initData(): void
    {
        $this->data = [
            'roomTypes' => $this->roomTypeRepository->findAllRaw(),
            'tariffs' => $this->tariffRepository->findAllRaw(),
            'hotels' => $this->hotelRepository->findAllRaw(),
            'categories' => $this->categoryRepository->findAllRaw(),
            'promotions' => $this->promotionRepository->findAllRaw()
        ];
    }


    private function getRoomTypesInfo(): array
    {
        array_walk($this->data['roomTypes'], function (&$roomType) {
            $hotel = $this->data['hotels'][(string)$roomType['hotel']['$id']];
            $roomType['priority'] = $hotel['isDefault'] ? 10 : 0;
        });

        return $this->data['roomTypes'];

    }

    private function getTariffsInfo(): array
    {
        return $this->data['tariffs'];
    }

    private function getHotelsInfo(): array
    {
        return $this->data['hotels'];
    }

    private function getRoomTypeCategoriesInfo(): array
    {
        return $this->data['categories'];
    }

    private function getPromotions(): array
    {
        return $this->data['promotions'];
    }

    private function getImagesInfo()
    {
        return [];
    }


}