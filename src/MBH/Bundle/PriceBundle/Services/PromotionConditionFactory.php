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
     * @param Promotion|null $promotion
     * @param $price
     * @return float
     */
    public static function calcDiscount(Promotion $promotion =  null, $price)
    {
        $total = 0;
        if ($promotion && $promotion->getDiscount()) {
            $total = $promotion->getisPercentDiscount() ? $price * $promotion->getDiscount() / 100 : $promotion->getDiscount();
        }

        return $total;
    }

    /**
     * @param Promotion|null $promotion
     * @param int $length
     * @param int $guests
     * @return bool
     */
    public static function checkConditions(Promotion $promotion =  null, $length = 0, $guests = 0)
    {
        if (!$promotion) {
            return false;
        }

        if (!$promotion->getConditionQuantity() || !$promotion->getCondition()) {
            return true;
        }

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
        ];
    }
}