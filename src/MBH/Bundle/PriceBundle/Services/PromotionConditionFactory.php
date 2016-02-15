<?php

namespace MBH\Bundle\PriceBundle\Services;
use MBH\Bundle\PriceBundle\Document\Promotion;

/**
 * Class PromotionConditionStrategyFactory
 * @package MBH\Bundle\PriceBundle\Services
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PromotionConditionFactory
{
    /**
     * Минимальная длинна брони
     */
    const CONDITION_MIN_ACCOMMODATION = 'min_accommodation';
    /**
     * Максимальная блинна брони
     */
    const CONDITION_MAX_ACCOMMODATION = 'max_accommodation';
    /**
     * Минимальное еоличество гостей
     */
    const CONDITION_MAX_TOURISTS = 'max_tourists';
    /**
     * Максимальное количество гостей
     */
    const CONDITION_MIN_TOURISTS = 'min_tourists';

    /**
     * Максимальное количество взрослых
     */
    const CONDITION_MIN_ADULTS = 'min_adults';

    /**
     * Минимальное количество взрослых
     */
    const CONDITION_MAX_ADULTS = 'max_adults';

    /**
     * @param Promotion|null $promotion
     * @param $price
     * @param bool|false $day
     * @return float|int
     */
    public static function calcDiscount(Promotion $promotion =  null, $price, $day = false)
    {
        $total = 0;
        if ($promotion && $promotion->getDiscount()) {

            if ($day && !$promotion->getisPercentDiscount()) {
                return $total;
            }
            if (!$day && $promotion->getisPercentDiscount()) {
                return $total;
            }

            $total = $promotion->getisPercentDiscount() ? $price * $promotion->getDiscount() / 100 : $promotion->getDiscount();
        }

        return $total;
    }

    /**
     * @param Promotion|null $promotion
     * @param int $length
     * @param int $adults
     * @param int $children
     * @return bool
     */
    public static function checkConditions(Promotion $promotion =  null, $length = 0, $adults = 0, $children = 0)
    {
        if (!$promotion) {
            return false;
        }

        if (!$promotion->getConditionQuantity() || !$promotion->getCondition()) {
            return true;
        }

        $guests = $adults + $children;

        switch ($promotion->getCondition()) {
            case self::CONDITION_MAX_ACCOMMODATION:
                if ($length <= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_ACCOMMODATION:
                if ($length >= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            case self::CONDITION_MAX_TOURISTS:
                if ($guests <= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_TOURISTS:
                if ($guests >= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_ADULTS:
                if ($adults >= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            case self::CONDITION_MAX_ADULTS:
                if ($adults <= $promotion->getConditionQuantity()) {
                    return true;
                }
                break;
            default:
                return false;
        }

        return false;
    }

    public static function getAvailableConditions()
    {
        return [
            self::CONDITION_MIN_ACCOMMODATION,
            self::CONDITION_MAX_ACCOMMODATION,
            self::CONDITION_MAX_TOURISTS,
            self::CONDITION_MIN_TOURISTS,
            self::CONDITION_MAX_ADULTS,
            self::CONDITION_MIN_ADULTS,
        ];
    }
}