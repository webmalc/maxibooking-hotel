<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 18.12.16
 * Time: 15:48
 */

namespace MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok;


/**
 * Class OstrovokDataGenerator
 * @package MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok
 */
class OstrovokDataGenerator
{
    /**
     * @param $roomCategory
     * @param $count
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param $hotelId
     * @return array
     */
    public function getRequestDataRnaRoomAmount($roomCategory, $count, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return ['room_categories' => [$this->getRnaRoomCategoriesData($roomCategory, $count, $startDate, $endDate, $hotelId)]];
    }

    /**
     * @param $roomCategory
     * @param $count
     * @param \DateTime|null $startDate
     * @param int $days
     * @param $hotelId
     * @return array
     */
    public function getRequestDataRnaRoomCategoriesByEveryDay($roomCategory, $count, \DateTime $startDate = null, $days = 365, $hotelId)
    {
        $arrData = [];
        for ($i = 0; $i < $days; $i++) {
            $endDate = (clone $startDate)->modify("+1 day");
            $arrData[] = $this->getRequestDataRnaRoomAmount($roomCategory, $count, $startDate, $endDate, $hotelId);
        }
        return ['room_categories' => [$arrData]];
    }


    /**
     * @param $occupancyId
     * @param $roomCategory
     * @param $ratePlan
     * @param null $price
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param $hotelId
     * @return array
     */
    public function getRequestDataRnaPrice($occupancyId, $roomCategory, $ratePlan, $price = null, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return ['occupancies' => [$this->getRnaOccupanciesData($occupancyId, $roomCategory, $ratePlan, $price, $startDate, $endDate, $hotelId)]];
    }

    /**
     * @param int $roomCategory
     * @param int $ratePlan
     * @param int $hotelId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int|null $minStayArrival
     * @param int|null $maxStayArrival
     * @param int|null $minStayThrough
     * @param int|null $maxStayThroug
     * @param bool|null $closeOnArrival
     * @param bool|null $closeOnDeparture
     * @return array
     */
    public function getRequestDataRnaRestrictions(int $roomCategory, int $ratePlan, int $hotelId, \DateTime $start, \DateTime $end, int $minStayArrival = null, int $maxStayArrival = null, int $minStayThrough = null, int $maxStayThroug = null, bool $closeOnArrival = false, bool $closeOnDeparture = false)
    {

        return ['rate_plans' => [$this->getRnaRestrictionData( $roomCategory,  $ratePlan,  $hotelId,  $start,  $end,  $minStayArrival,  $maxStayArrival,  $minStayThrough,  $maxStayThroug, $closeOnArrival, $closeOnDeparture)]];
    }

    /**
     * @param $occupancyId
     * @param $roomCategory
     * @param $ratePlan
     * @param null $price
     * @param \DateTime $start
     * @param \DateTime $end
     * @param $hotelId
     * @return array
     */
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

    /**
     * @param int $roomCategory
     * @param int $ratePlan
     * @param int $hotelId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int|null $minStayArrival
     * @param int|null $maxStayArrival
     * @param int|null $minStayThrough
     * @param int|null $maxStayThroug
     * @param bool|null $closeOnArrival
     * @param bool|null $closeOnDeparture
     * @return array
     */
    private function getRnaRestrictionData(int $roomCategory, int $ratePlan, int $hotelId, \DateTime $start, \DateTime $end, int $minStayArrival = null, int $maxStayArrival = null, int $minStayThrough = null, int $maxStayThroug = null, bool $closeOnArrival = false, bool $closeOnDeparture = false)
    {
        return [
            'disable_flexible' => false,
            'last_minute' => null,
            'advance' => null,
            'plan_date_start_at' => $start->format('Y-m-d'),
            'plan_date_end_at' => $end->format('Y-m-d'),
            'room_category' => $roomCategory,
            'rate_plan' => $ratePlan,
            'hotel' => $hotelId,
            'min_stay_arrival' => $minStayArrival,
            'max_stay_arrival' => $maxStayArrival,
            'min_stay_through' => $minStayThrough,
            'max_stay_through' => $maxStayThroug,
            'closed_on_arrival' => $closeOnArrival,
            'closed_on_departure' => $closeOnDeparture,
            'format' => 'json'];
    }


    /**
     * @param $roomCategory
     * @param $count
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param $hotelId
     * @return array
     */
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





}