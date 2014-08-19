<?php

namespace MBH\Bundle\BaseBundle\Service;


/**
 * Helper service
 */
class Helper
{
    /**
     * @param int $length
     * @return string
     *
     */
    public function getRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * @param string = $string
     * @param string $format
     * @return \DateTime
     */
    public function getDateFromString($string, $format = "d.m.Y")
    {
        return \DateTime::createFromFormat($format . ' H:i:s', $string . ' 00:00:00');
    }
}
