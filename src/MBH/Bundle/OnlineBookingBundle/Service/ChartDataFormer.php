<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 13.01.17
 * Time: 16:27
 */

namespace MBH\Bundle\OnlineBookingBundle\Service;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Services\Calculation;
use MBH\Bundle\PriceBundle\Document\Tariff;

class ChartDataFormer
{

    private $priceCalculator;

    /**
     * ChartDataFormer constructor.
     * @param $priceCalculator
     */
    public function __construct(Calculation $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }


    public function getPriceCalendarData(
        RoomType $roomType,
        Tariff $tariff,
        int $adults,
        int $children,
        \DateTime $packageBegin,
        \DateTime $packageEnd,
        int $period = 15
    )
    {
        $data = null;
        $prices = [];

        if ($packageBegin->modify('midnight') instanceof \DateTime && $packageEnd->modify(
                'midnight'
            ) instanceof \DateTime
        ) {
            $packagePeriod = (int)$packageBegin->diff($packageEnd)->format('%d');
            $begin = (clone $packageBegin)->modify('-'.($period).' days');
            $end = (clone $packageBegin)->modify('+'.($period + $packagePeriod).' days');
            $rawPrices = $this->priceCalculator->calcPrices(
                $roomType,
                $tariff,
                $begin->modify('midnight'),
                $end->modify('midnight'),
                $adults,
                $children,
                $tariff->getDefaultPromotion(),
                true,
                false
            );

            if (!count($rawPrices)) {
                return null;
            }

            $rawPrices = $rawPrices[$adults.'_'.$children]['prices'];
            $fmt = new \IntlDateFormatter(
                'ru_RU',
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                'Europe/Moscow',
                null,
                "d MMMM"
            );
            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                $price = null;
                if (isset($rawPrices[$day->format('d_m_Y')])) {
                    $price = $rawPrices[$day->format('d_m_Y')];
                } else {
                    $price = null;
                }

                $prices[] = [
                    'name' => $fmt->format($day),
                    'y' => $price,
                    'color' => ($day >= $packageBegin && $day <= $packageEnd) ? 'green' : null,
                ];
            }

            $center = ($period + $period + $packagePeriod) / 2;
            $showBegin = $center - $packagePeriod / 2 - 4;
            $showEnd = $center + $packagePeriod / 2 + 4;
            $data['showBegin'] = $showBegin;
            $data['showEnd'] = $showEnd;
        }


        $data['prices'] = $prices;
        $data['tariffName'] = $tariff->getName();
        $data['roomTypeName'] = $roomType->getName();


        return $data;
    }

}