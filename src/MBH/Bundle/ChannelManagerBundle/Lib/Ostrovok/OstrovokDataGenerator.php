<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 18.12.16
 * Time: 15:48
 */

namespace MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PriceBundle\Document\Tariff;


/**
 * Class OstrovokDataGenerator
 * @package MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok
 */
class OstrovokDataGenerator
{
    /**
     * @param $CMRoomCategory
     * @param $count
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param $hotelId
     * @return array
     */
    public function getRequestDataRnaRoomAmount($CMRoomCategory, $count, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        return ['room_categories' => [$this->getRnaRoomCategoriesData($CMRoomCategory, $count, $startDate, $endDate, $hotelId)]];
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
    public function getRequestDataRnaRestrictions(int $roomCategory, int $ratePlan, int $hotelId, \DateTime $start, \DateTime $end, int $minStayArrival = null, int $maxStayArrival = null, int $minStayThrough = null, int $maxStayThroug = null, bool $closeOnArrival = false, bool $closeOnDeparture = false, bool $disableFlexible = false)
    {

        return ['rate_plans' => [$this->getRnaRestrictionData($roomCategory, $ratePlan, $hotelId, $start, $end, $minStayArrival, $maxStayArrival, $minStayThrough, $maxStayThroug, $closeOnArrival, $closeOnDeparture, $disableFlexible)]];
    }

    public function getRequestDataRatePlan(Tariff $tariff, $ratePlan, ChannelManagerConfigInterface $config)
    {
        $min_stay = null;
        $max_stay = null;

        if ($tariff->getCondition() == 'max_accommodation') {
            $max_stay = $tariff->getConditionQuantity();
        } elseif ($tariff->getAdditionalCondition() == 'max_accommodation') {
            $max_stay = $tariff->getAdditionalConditionQuantity();
        }
        if ($tariff->getCondition() == 'min_accommodation') {
            $min_stay = $tariff->getConditionQuantity();
        } elseif ($tariff->getAdditionalConditionQuantity() == 'min_accommodation') {
            $min_stay = $tariff->getAdditionalConditionQuantity();
        }


        //TODO Не смог получить процент скидки из акции в дочернем тарифе.
        $hasParent = (bool)$ratePlan['parent'];

        if ($hasParent) {
            $discount = $ratePlan['discount'];
            $discountUnit = $ratePlan['discount_unit'];
            $dis = null;
        }

        return [
            'advance' => $ratePlan['advance'],
            'last_minute' => $ratePlan['last_minute'],
            'free_nights' => $ratePlan['free_nights'],
            'discount' => $ratePlan['discount'],
            'discount_unit' => $ratePlan['discount_unit'],
            'meal_plan' => $ratePlan['meal_plan'],
            'meal_plan_cost' => $ratePlan['meal_plan_cost'],
            'meal_plan_available' => $ratePlan['meal_plan_available'],
            'meal_plan_included' => $ratePlan['meal_plan_included'],
            'cancellation_available' => $ratePlan['cancellation_available'],
            'cancellation_lead_time' => $ratePlan['cancellation_lead_time'],
            'cancellation_penalty_nights' => $ratePlan['cancellation_penalty_nights'],
            'deposit_available' => $ratePlan['deposit_available'],
            'deposit_returnable' => $ratePlan['deposit_returnable'],
            'deposit_rate' => $ratePlan['deposit_rate'],
            'deposit_unit' => $ratePlan['deposit_unit'],
            'no_show_rate' => $ratePlan['no_show_rate'],
            'no_show_unit' => $ratePlan['no_show_unit'],
            'plan_date_start_at' => $tariff->getBegin()?$tariff->getBegin()->format('Y-m-d'):null,
            'plan_date_end_at' => $tariff->getEnd()?$tariff->getEnd()->format('Y-m-d'):null,
            'booking_date_start_at' => $ratePlan['booking_date_start_at'],
            'booking_date_end_at' => $ratePlan['booking_date_end_at'],
            'min_stay_arrival' => $min_stay,
            'max_stay_arrival' => $max_stay,
            'min_stay_through' => $ratePlan['min_stay_through'],
            'max_stay_through' => $ratePlan['max_stay_through'],
            'status' => $ratePlan['status'],
            'name' => $ratePlan['name'],
            'description' => $ratePlan['description'],
            'external_id' => $ratePlan['external_id'],
            'id' => $ratePlan['id'],
            'room_category' => $ratePlan['room_category'],
            'hotel' => $ratePlan['hotel']
        ];
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
    private function getRnaRestrictionData(int $roomCategory, int $ratePlan, int $hotelId, \DateTime $start, \DateTime $end, int $minStayArrival = null, int $maxStayArrival = null, int $minStayThrough = null, int $maxStayThroug = null, bool $closeOnArrival = false, bool $closeOnDeparture = false, bool $disableFlexible = false)
    {
        return [
            'disable_flexible' => $disableFlexible,
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
     * @param $CMRoomCategory
     * @param $count
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param $hotelId
     * @return array
     */
    public function getRnaRoomCategoriesData($CMRoomCategory, $count, \DateTime $startDate = null, \DateTime $endDate = null, $hotelId)
    {
        if ($count <= 0) {
            $count = 0;
        }

        return [
            'hotel' => $hotelId,
            'count' => $count,
            'room_category' => $CMRoomCategory,
            'plan_date_start_at' => $startDate->format('Y-m-d'),
            'plan_date_end_at' => $endDate->format('Y-m-d'),
            'format' => 'json'
        ];
    }


}