<?php

namespace MBH\Bundle\BaseBundle\Lib;


class CallbacksLibrary
{
    public static function getDateTimeFormCallback($format)
    {
        return function (\DateTime $dateTime) use ($format) {
            return $dateTime->format($format);
        };
    }
}