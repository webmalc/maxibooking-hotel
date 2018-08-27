<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib;


abstract class PaymentSystemDocument
{
    /**
     * @return string
     */
    public static function fileClassName(): string
    {
        $name = explode('\\', static::class);

        return end($name);
    }

    /**
     * For dynamic class names
     *
     * @return string
     */
    public static function className(): string
    {
        return static::class;
    }
}