<?php

namespace MBH\Bundle\OnlineBookingBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\SpecialDataPreparerExeption;
use MBH\Bundle\OnlineBookingBundle\Service\Sorters\SorterInterface;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;
use MBH\Bundle\PriceBundle\Document\SpecialRepository;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;

/**
 * Class SpecialDataPreparer
 * @package MBH\Bundle\OnlineBookingBundle\Service
 */
class SpecialDataPreparer
{

    const NO_IMAGE_PATH = 'noimage.png';

    const ONLY_DEFAULT_TARIFF = false;

    /** @var  DocumentManager */
    private $dm;

    private $onlineOptions;

    /** @var  SorterInterface */
    private $sorter;

    /**
     * SpecialDataPreparer constructor.
     * @param DocumentManager $dm
     * @param array $onlineOptions
     * @param SorterInterface $sorter
     * @param array $onlineBookingConfig
     */
    public function __construct(DocumentManager $dm, array $onlineOptions, SorterInterface $sorter)
    {
        $this->dm = $dm;
        $this->onlineOptions = $onlineOptions;
        $this->sorter = $sorter;
    }

    /**
     * @param Hotel|null $hotel
     * @param \DateTime|null $begin
     * @return \Doctrine\MongoDB\CursorInterface
     */
    public function getSpecials(Hotel $hotel = null, \DateTime $begin = null)
    {
        $specialsFilter = new SpecialFilter();
        $specialsFilter->setRemain(1);
        if (!$begin) {
            $begin = new \DateTime("now midnight");
        }
        $specialsFilter->setBegin($begin);
        if ($hotel) {
            $specialsFilter->setHotel($hotel);
        }

        /** @var SpecialRepository $specialRepo */
        $specialRepo = $this->dm->getRepository('MBHPriceBundle:Special');

        return $specialRepo->getStrictBeginFiltered($specialsFilter);
    }

    /**
     * @param array $specials
     * @return array
     */
    public function getSpecialsPageFormatWithMonth(array $specials): array
    {
        $results = [];
        $data = $this->getSpecialsPageFormat($specials);
        foreach ($data['specials'] as $specialEntity) {
            /** @var \DateTime $begin */
            /** @var \DateTime $end */
            $begin = $specialEntity['dates']['begin'];
            $end = $specialEntity['dates']['end'];
            $special = $specialEntity['special'];
            $results['month_' . $begin->format('m')][] = $special->getId();
            $results['month_' . $end->format('m')][] = $special->getId();

        }
        $data['byMonth'] = $results;
        $data = $this->uniqueByMonthFilter($data);

        return $data;
    }

    /**
     * @param array $specials
     * @return array
     */
    public function getSpecialsPageFormat(array $specials): array
    {
        $result = [];
        foreach ($specials as $special) {
            if (!($special instanceof Special) || !count($special->getRoomTypes()) || $special->isRecalculation() || !$special->getRemain() || !empty($special->getError())) {
                continue;
            }
            if (count($special->getPrices())) {
                foreach ($special->getPrices() as $specialPrice) {
                    $isDefaultTariff = $specialPrice->getTariff()->getIsDefault();
                    if (self::ONLY_DEFAULT_TARIFF && !$isDefaultTariff) {
                        continue;
                    }

                    $result[] = $this->prepareDataToTwig($specialPrice, $special);
                }
            }

        }

        return ['specials' => $this->sorter->sort($result)];
    }

    /**
     * @param SpecialPrice $specialPrice
     * @param Special $special
     * @return array
     */
    private function prepareDataToTwig(SpecialPrice $specialPrice, Special $special): array
    {
        /** @var SpecialPrice $specPrice */
        $roomType = $specialPrice->getRoomType();
        $tariff = $specialPrice->getTariff();
        $hotel = $roomType->getHotel();
        $hotelId = $hotel->getId();

        $result = [
            'special' => $special,
            'roomType' => $roomType,
            'tariff' => $tariff,
            'hotelId' => $hotelId,
            'images' => $this->getImage($roomType),
            'hotelName' => $roomType->getHotel()->getName(),
            'roomTypeName' => $roomType->getCategory()->getFullTitle(),
            'eat' => $this->getEatData($hotelId),
            'dates' => [
                'begin' => $special->getBegin(),
                'end' => $special->getEnd(),
                'days' => $special->getDays(),
                'nights' => $special->getNights(),
            ],
            'discount' => $special->getDiscount(),
            'isPercent' => $special->isIsPercent(),
            'prices' => $specialPrice->getPrices(),
            'default_price' => $special->getDefaultPrice()?:$this->determineDefaultPrice($specialPrice->getPrices()),
            'specialId' => $special->getId(),
            'roomTypeId' => $roomType->getId(),
            'roomCategoryId' => $roomType->getCategory()->getId(),
            'hotelLink' => $this->onlineOptions['hotels_links'][$hotelId]??null
        ];

        return $result;
    }

    private function getEatData(string $hotelId): ?array
    {
        return $this->onlineOptions['hotels_links'][$hotelId]['eat'] ?? null;
    }


    /**
     * Определяем наибольшее количество человек с наименьшей ценой
     * @param array|null $prices
     * @return null|string
     */
    private function determineDefaultPrice(array $prices = null): ?string
    {
        $result = '';
        if (is_array($prices)) {
//            /** Вариант наивыгоднейшей цены */
//             * Сортировка по условию минимум цена за одного
//             */
//            $price = $capacity = [];
//            $origin = $prices;
//            uksort($prices, function ($capA, $capB) use($origin) {
//                [$adultsA, $childrenA] = explode('_', $capA);
//                [$adultsB, $childrenB] = explode('_', $capB);
//                $capacityA = $adultsA + $childrenA;
//                $capacityB = $adultsB + $childrenB;
//
//                $priceA = $origin[$capA] / $capacityA;
//                $priceB = $origin[$capB] / $capacityB;
//
//                return $priceA <=> $priceB;
//            });

            /** Вариант 2, без цены за 1
             * Принудительно убираю цену за одного из значений по-умолчанию.
             *
             * @var  $key
             * @var  $value
             */
            $capacity = [];
            $excludeCapacity = '1_0';
            if (isset($prices[$excludeCapacity])) {
                unset($prices[$excludeCapacity]);
            }
            foreach ($prices as $key => $value) {
                $price[] = $value;
                $capacity[] = $key;
            }
            array_multisort($price, SORT_ASC, SORT_NUMERIC, $capacity, SORT_DESC, SORT_STRING);
        }

        if (!empty($capacity) && is_array($capacity)) {
            $result = reset($capacity);
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function uniqueByMonthFilter(array $data): array
    {
        if (!$data['byMonth']??false) {
            return $data;
        }
        foreach ($data['byMonth'] as $byMonthKey => $value) {
            $data['byMonth'][$byMonthKey] = array_unique($value);
        }

        return $data;
    }


    private function getImage(RoomType $roomType): array
    {
        $roomImage = $roomType->getMainImage()??$roomType->getImages()->first()??null;
        $hotelImage = $roomType->getHotel()->getImages()->first()??null;
        $mainImage = $roomImage??$hotelImage;
        if ($mainImage instanceof Image || $mainImage instanceof RoomTypeImage) {
            $result[] = $mainImage->getPath();
        } else {
            $result[] = self::NO_IMAGE_PATH;
        }

        foreach ($roomType->getImages() as $image) {
            if (!$image->getIsMain()) {
                $result[] = $image->getPath();
            }
        }

        return $result;
    }

}