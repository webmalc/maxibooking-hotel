<?php

namespace MBH\Bundle\PriceBundle\Services;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Lib\ConditionsInterface;

/**
 * Class PromotionConditionStrategyFactory
 * @package MBH\Bundle\PriceBundle\Services

 */
class PromotionConditionFactory
{
    /**
     * Minimum booking length
     */
    const CONDITION_MIN_ACCOMMODATION = 'min_accommodation';
    /**
     * Maximum booking lenght
     */
    const CONDITION_MAX_ACCOMMODATION = 'max_accommodation';
    /**
     * Maximum count guests
     */
    const CONDITION_MAX_TOURISTS = 'max_tourists';
    /**
     * Minimum count guests
     */
    const CONDITION_MIN_TOURISTS = 'min_tourists';

    /**
     * Minimum count adults
     */
    const CONDITION_MIN_ADULTS = 'min_adults';

    /**
     * Maximum count adults
     */
    const CONDITION_MAX_ADULTS = 'max_adults';

    /**
     * Minimum count children
     */
    const CONDITION_MIN_CHILDREN = 'min_children';

    /**
     * Maximum count children
     */
    const CONDITION_MAX_CHILDREN = 'max_children';

    /**
     * @param Promotion|null $promotion
     * @param $price
     * @param bool|false $day
     * @return float|int
     */
    public static function calcDiscount(?Promotion $promotion =  null, $price, $day = false)
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
     * @param $condition
     * @param $quantity
     * @param $adults
     * @param $children
     * @param $length
     * @return bool
     */
    static public function checkCondition($condition, $quantity, $adults, $children, $length)
    {
        if (!$condition || !$quantity) {
            return true;
        }
        $guests = $adults + $children;
        if (!$guests && !in_array($condition, [
                self::CONDITION_MAX_ACCOMMODATION,
                self::CONDITION_MIN_ACCOMMODATION
            ])) {
            return true;
        }

        switch ($condition) {
            case self::CONDITION_MAX_ACCOMMODATION:
                if ($length <= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_ACCOMMODATION:
                if ($length >= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MAX_TOURISTS:
                if ($guests <= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_TOURISTS:
                if ($guests >= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_ADULTS:
                if ($adults >= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MAX_ADULTS:
                if ($adults <= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MIN_CHILDREN:
                if ($children >= $quantity) {
                    return true;
                }
                break;
            case self::CONDITION_MAX_CHILDREN:
                if ($children <= $quantity) {
                    return true;
                }
                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * @param ConditionsInterface|null $promotion
     * @param int $length
     * @param int $adults
     * @param int $children
     * @return bool
     */
    public static function checkConditions(ConditionsInterface $promotion =  null, $length = 0, $adults = 0, $children = 0)
    {
        if (!$promotion) {
            return false;
        }
        $main = self::checkCondition(
            $promotion->getCondition(), $promotion->getConditionQuantity(), $adults, $children, $length
        );

        $add = self::checkCondition(
            $promotion->getAdditionalCondition(), $promotion->getAdditionalConditionQuantity(), $adults, $children, $length
        );

        return $main && $add;
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
            self::CONDITION_MAX_CHILDREN,
            self::CONDITION_MIN_CHILDREN
        ];
    }
}