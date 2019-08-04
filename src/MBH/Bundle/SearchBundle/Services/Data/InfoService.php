<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Liip\ImagineBundle\Templating\Helper\FilterHelper;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\PromotionRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Asset\Packages;

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
     * @var array
     */
    private $azSettings;
    /**
     * @var HttpFoundationExtension
     */
    private $router;
    /**
     * @var Packages
     */
    private $packages;
    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * InfoService constructor.
     * @param RoomTypeRepository $roomTypeRepository
     * @param TariffRepository $tariffRepository
     * @param HotelRepository $hotelRepository
     * @param RoomTypeCategoryRepository $categoryRepository
     * @param PromotionRepository $promotionRepository
     * @param array $azSettings
     * @param HttpFoundationExtension $router
     * @param Packages $packages
     * @param FilterHelper $filterHelper
     */
    public function __construct(
        RoomTypeRepository $roomTypeRepository,
        TariffRepository $tariffRepository,
        HotelRepository $hotelRepository,
        RoomTypeCategoryRepository $categoryRepository,
        PromotionRepository $promotionRepository,
        array $azSettings,
        HttpFoundationExtension $router,
        Packages $packages,
        FilterHelper $filterHelper

    ) {
        $this->roomTypeRepository = $roomTypeRepository;
        $this->tariffRepository = $tariffRepository;
        $this->hotelRepository = $hotelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->promotionRepository = $promotionRepository;
        $this->azSettings = $azSettings;
        $this->router = $router;
        $this->packages = $packages;
        $this->filterHelper = $filterHelper;
    }


    public function getInfo(): array
    {
        $this->initData();

        return [
            'roomTypes' => $this->getRoomTypesInfo(),
            'tariffs' => $this->getTariffsInfo(),
            'hotels' => $this->getHotelsInfo(),
            'categories' => $this->getRoomTypeCategoriesInfo(),
            'promotions' => $this->getPromotions(),
        ];
    }

    private function initData(): void
    {
        $this->data = [
            'roomTypes' => $this->roomTypeRepository->findAllRaw(),
            'tariffs' => $this->tariffRepository->findAllRaw(),
            'hotels' => $this->hotelRepository->findAllRaw(),
            'categories' => $this->categoryRepository->findAllRaw(),
            'promotions' => $this->promotionRepository->findAllRaw(),
        ];
    }


    private function getRoomTypesInfo(): array
    {
        array_walk(
            $this->data['roomTypes'],
            function (&$roomType) {
                $hotel = $this->data['hotels'][(string)$roomType['hotel']['$id']];
                $roomType['priority'] = $hotel['isDefault'] ? 10 : 0;
                if (($images = $roomType['images'] ?? null) && count($images)) {
                    $roomType['frontImages'] = $this->getImagesInfo($images);
                }
            }
        );

        return $this->data['roomTypes'];

    }

    private function getTariffsInfo(): array
    {
        return $this->data['tariffs'];
    }

    private function getHotelsInfo(): array
    {
        array_walk(
            $this->data['hotels'],
            function (&$hotel) {
                $hotelsLinks = $this->azSettings['hotels_links'];
                $links = $hotelsLinks[(string)$hotel['_id']] ?? null;
                if (null !== $links) {
                    $hotel['links'] = $links;
                }

            }
        );

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

    private function getImagesInfo($images)
    {
        $result = [];
        foreach ($images as $image) {
            $result[] = [
                'isMain' => $image['isMain'],
                'src' => $this->router->generateAbsoluteUrl($this->packages->getUrl($image['path'])),
                'thumb' => $this->filterHelper->filter($image['path'], 'thumb_275x210'),
            ];
        }

        return $result;
    }

}