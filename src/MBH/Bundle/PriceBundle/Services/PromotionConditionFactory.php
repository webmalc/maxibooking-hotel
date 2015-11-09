<?php

namespace MBH\Bundle\PriceBundle\Services;

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
    const CONDITION_MAX_TOURISTS = 'min_tourists';
    /**
     * Максимальное количество гостей
     */
    const CONDITION_MIN_TOURISTS = 'max_tourists';

    /**
     * @param $constant
     * @return PromotionConditionInterface
     */
    public function create($constant)
    {
        //todo...
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