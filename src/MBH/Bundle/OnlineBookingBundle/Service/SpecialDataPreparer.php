<?php

namespace MBH\Bundle\OnlineBookingBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\SpecialDataPreparerExeption;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;

class SpecialDataPreparer
{

    const NO_IMAGE_PATH = 'noimage.png';

    const ONLY_DEFAULT_TARIFF = true;

    /** @var  DocumentManager */
    private $dm;

    /**
     * SpecialDataPreparer constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getPreparedData(array $specials): array
    {
        $result = [];
        foreach ($specials as $special) {
            if (!($special instanceof Special) || !count($special->getRoomTypes()) || $special->isRecalculation()) {
                continue;
            }
            foreach ($special->getPrices() as $specialPrice) {
                $isDefaultTariff = $specialPrice->getTariff()->getIsDefault();
                if (self::ONLY_DEFAULT_TARIFF && !$isDefaultTariff ) {
                    continue;
                }

                $result[] = $this->prepareDataToTwig($specialPrice, $special);
            }
        }

        return $result;
    }

    private function prepareDataToTwig(SpecialPrice $specialPrice, Special $special): array
    {
        /** @var SpecialPrice $specPrice */
        $roomType = $specialPrice->getRoomType();
        $tariff = $specialPrice->getTariff();


        $result = [
            'special' => $special,
            'roomType' => $roomType,
            'tariff' => $tariff,
            'hotelId' => $roomType->getHotel()->getId(),
            'images' => $this->getImage($roomType),
            'hotelName' => $roomType->getHotel()->getName(),
            'roomTypeName' => $roomType->getName(),
            'eat' => '',
            'dates' => [
                'begin' => $special->getBegin(),
                'end' => $special->getEnd(),
                'days' => $special->getDays(),
                'nights' => $special->getNights(),
            ],
            'discount' => $special->getDiscount(),
            'isPercent' => $special->isIsPercent(),
            'prices' => $specialPrice->getPrices(),
            'specialId' => $special->getId(),
            'roomTypeId' => $roomType->getId(),
            'roomCategoryId' => $roomType->getCategory()->getId(),
            /*'virtualRoomId' => $special->getVirtualRoom()->getId()*/
        ];

        return $result;
    }

    private function getPrices(array $prices): array
    {
        $result = [];
        foreach ($prices as $priceKey => $price) {
            $result[$priceKey] = $price['total'];
        }

        return $result;
    }
    private function getImage(RoomType $roomType): array
    {
        $roomImage = $roomType->getMainImage()??$roomType->getImages()->first()??null;
        $hotelImage = $roomType->getHotel()->getImages()->first()??null;
        $mainImage = $roomImage??$hotelImage;
        if ($mainImage instanceof Image || $mainImage instanceof RoomTypeImage) {
            $result[] = $mainImage->getPath();
        } else {
            $result = self::NO_IMAGE_PATH;
        }

        foreach ($roomType->getImages() as $image) {
            if (!$image->getIsMain()) {
                $result[] = $image->getPath();
            }
        }

        return $result;
    }

}