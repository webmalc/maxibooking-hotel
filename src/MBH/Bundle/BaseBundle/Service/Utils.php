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
}