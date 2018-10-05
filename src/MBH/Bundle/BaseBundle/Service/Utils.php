<?php

namespace MBH\Bundle\BaseBundle\Service;

/**
 * Service for simple static functions
 *
 * Class Utils
 * @package MBH\Bundle\BaseBundle\Service
 */
class Utils
{
    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function getFromArrayByKeys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * If date > date2 than return negative int
     * @param \DateTime $date
     * @param \DateTime $date2
     * @return int
     */
    public static function getDifferenceInDaysWithSign(\DateTime $date, \DateTime $date2)
    {
        return (int)($date->diff($date2)->format("%r%a"));
    }

    /**
     * @param $value
     * @return bool
     */
    public static function canBeCastedToBool($value)
    {
        return is_bool($value) || in_array($value, ['true', 'false']);
    }

    public static function canConvertToString($value)
    {
        return !is_array($value)
            && ((!is_object($value) && settype($value, 'string') !== false) ||
                (is_object($value) && method_exists($value, '__toString')));
    }

    /**
     * @param $value
     * @return string
     */
    public static function getStringValueOrType($value)
    {
        if (Utils::canConvertToString($value)) {
            return (string)$value;
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    public static function castIterableToArray($value)
    {
        if (!is_iterable($value)) {
            throw new \InvalidArgumentException('Passed value should be iterable');
        }

        return is_array($value) ? $value : iterator_to_array($value);
    }
}