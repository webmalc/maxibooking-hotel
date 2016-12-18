<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 18.12.16
 * Time: 15:48
 */

namespace MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok;


class OstrovokDataGenerator
{
    public function getRequestDataRnaRoomAmount($roomCategory, $count, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return ['room_categories' => [$this->getRnaRoomCategoriesData($roomCategory, $count, $startDate, $endDate, $hotelId)]];
    }

    public function getRequestDataRnaRoomCategoriesByEveryDay($roomCategory, $count, \DateTime $startDate = null, $days = 365, $hotelId)
    {
        $arrData = [];
        for ($i = 0; $i < $days; $i++) {
            $endDate = (clone $startDate)->modify("+1 day");
            $arrData[] = $this->getRequestDataRnaRoomAmount($roomCategory, $count, $startDate, $endDate, $hotelId);
        }
        return ['room_categories' => [$arrData]];
    }

    private function getRnaRoomCategoriesData($roomCategory, $count, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return [
            'hotel' => $hotelId,
            'count' => $count,
            'room_category' => $roomCategory,
            'plan_date_start_at' => $startDate->format('Y-m-d'),
            'plan_date_end_at' => $endDate->format('Y-m-d'),
            'format' => 'json'
        ];
    }

    public function getRequestDataRnaPrice($occupancyId, $roomCategory, $ratePlan, $price = null, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return ['occupancies' => [$this->getRnaOccupanciesData($occupancyId, $roomCategory, $ratePlan, $price, $startDate, $endDate, $hotelId)]];
    }

    private function getRnaOccupanciesData($occupancyId, $roomCategory, $ratePlan, $price = null, \DateTime $start, \DateTime $end, $hotelId)
    {
        return [
            'hotel' => $hotelId,
            'price' => $price,
            'occupancy' => $occupancyId,
            'room_category' => $roomCategory,
            'rate_plan' => $ratePlan,
            'plan_date_start_at' => $start->format('Y-m-d'),
            'plan_date_end_at' => $end->format('Y-m-d'),
            'format' => 'json'
        ];
    }


}