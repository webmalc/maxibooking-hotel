<?php


namespace MBH\Bundle\OnlineBookingBundle\Service;


use Liip\ImagineBundle\Templating\Helper\FilterHelper;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\SpecialConverterException;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\Asset\Packages;

/**
 * Class SpecialAzovskyConverter
 * @package MBH\Bundle\OnlineBookingBundle\Service
 */
class SpecialAzovskyConverter
{

    /** @var SpecialDataPreparer */
    private $preparer;

    /** @var array */
    private $onlineOptions;
    /**
     * @var Packages
     */
    private $packages;
    /**
     * @var HttpFoundationExtension
     */
    private $extension;
    /**
     * @var FilterHelper
     */
    private $imageHelper;

    /**
     * SpecialAzovskyConverter constructor.
     * @param SpecialDataPreparer $preparer
     * @param array $onlineOptions
     * @param Packages $packages
     * @param HttpFoundationExtension $extension
     * @param FilterHelper $imageHelper
     */
    public function __construct(
        SpecialDataPreparer $preparer,
        array $onlineOptions,
        Packages $packages,
        HttpFoundationExtension $extension,
        FilterHelper $imageHelper
    ) {
        $this->preparer = $preparer;
        $this->onlineOptions = $onlineOptions;
        $this->packages = $packages;
        $this->extension = $extension;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param Special[] $specials
     * @return array
     * @throws SpecialConverterException
     */
    public function convert(?array $specials): array
    {
        if (!$specials) {
            throw new SpecialConverterException('No specials');
        }

        $result = [];
        $timeFormat = 'd.m.Y';

        foreach ($specials as $special) {
            $hotel = $special->getHotel();
            $prices = $special->getPrices()->first();
            $instance = [
                'hotel' => $this->extractHotel($hotel),
                'dates' => [
                    'begin' => $special->getBegin()->format($timeFormat),
                    'end' => $special->getEnd()->format($timeFormat),
                ],
                'tariff' => $this->extractTariff($prices),
                'prices' => array_merge(
                    $this->extractPrices($prices),
                    [
                        'defaultPrice' => $special->getDefaultPrice() ?: $this->preparer->determineDefaultPrice(
                            $special->getPrices()->toArray()[0]->getPrices()
                        ),
                    ]
                ),
                'roomType' => $this->extractRoomType($prices),
                'special' => [
                    'id' => $special->getId(),
                    'name' => $special->getName(),
                    'discount' => $special->getDiscount(),
                ],
                'hotelLink' => $this->onlineOptions[$hotel->getId()] ?? null,

            ];
            $result[] = $instance;
            unset($instance);
        }


        return $result;
    }

    public function getFiltersFromResults(array $results): array
    {


        if (!$results) {
            throw new SpecialConverterException('No specials');
        }

        $filters = [
            'hotels' => $this->createHotelFilter(array_column($results, 'hotel')),
            'roomType' => $this->createRoomCategoryFilter(array_column($results, 'roomType')),
            'month' => $this->createMonthFilters(array_column($results, 'dates')),


        ];

        return $filters;

    }

    private function createHotelFilter(array $array)
    {
        $array = array_column($array, 'id', 'name');

        return $this->createFilter($array);
    }

    private function createRoomCategoryFilter(array $array)
    {
        $array = array_column($array, 'categoryId', 'categoryName');

        return $this->createFilter($array);
    }

    private function createRoomTypeFilters(array $roomTypes)
    {

    }

    private function createFilter(array $array)
    {
        $result = [];
        if (count($array)) {
            foreach ($array as $arrayName => $arrayValue) {
                $result[] = [
                    'text' => $arrayName,
                    'value' => $arrayValue,
                ];
            }

        }

        return $result;
    }




    private function createMonthFilters(array $dates)
    {

    }

    /**
     * @param SpecialPrice $prices
     * @return array
     */
    private function extractPrices(SpecialPrice $prices)
    {
        return [
            'prices' => $prices->getPrices(),
        ];

    }

    /**
     * @param SpecialPrice $price
     * @return array
     * @throws SpecialConverterException
     */
    private function extractTariff(SpecialPrice $price): array
    {
        $tariff = $price->getTariff();
        if (!$tariff) {
            throw new SpecialConverterException('No Tariff in Price');
        }
        if (($promotions = $tariff->getPromotions())->count()) {
            $promotion = $promotions->first();
        }
        $result = [
            'id' => $tariff->getId(),
            'name' => $tariff->getName(),
        ];

        /** @var Promotion $promotion */
        if ($promotion ?? null) {
            $result['promotion'] = [
                'id' => $promotion->getId(),
                'name' => $promotion->getName(),
                'discount' => $promotion->getDiscount(),
            ];
        }


        return $result;
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    private function extractHotel(Hotel $hotel): array
    {
        return [
            'id' => $hotel->getId(),
            'name' => $hotel->getName(),
        ];
    }

    /**
     * @param SpecialPrice $price
     * @return array
     * @throws SpecialConverterException
     */
    private function extractRoomType(SpecialPrice $price): array
    {
        $roomType = $price->getRoomType();
        if (!$roomType) {
            throw new SpecialConverterException('No roomType');
        }
        $category = $roomType->getCategory();

        if (!$category) {
            throw new SpecialConverterException('No Category');
        }

        return [
            'id' => $roomType->getId(),
            'name' => $roomType->getName(),
            'image' => $this->getImage($roomType),
            'categoryName' => $category->getFullTitle(),
            'categoryId' => $category->getId()
        ];
    }

    private function getImage(RoomType $roomType): array
    {
        $roomImage = $roomType->getMainImage() ?? $roomType->getImages()->first();
        $hotelImage = $roomType->getHotel()->getImages()->first();
        $mainImage = $roomImage ?? $hotelImage;
        $images = [];
        $result = [];
        if ($mainImage instanceof Image || $mainImage instanceof RoomTypeImage) {
            $images[] = $this->packages->getUrl($mainImage->getPath());
        } else {
            $result[] = '/no-image.jpg';
        }

        foreach ($roomType->getImages() as $image) {
            if (!$image->getIsMain()) {
                $images[] = $this->packages->getUrl($image->getPath());
            }
        }

        foreach ($images as $image) {
            $result[] = [
                'full' => $this->extension->generateAbsoluteUrl($this->packages->getUrl($image)),
                'thumb' => $this->imageHelper->filter($image, 'thumb_275x210'),
            ];
        }


        return $result;
    }


}