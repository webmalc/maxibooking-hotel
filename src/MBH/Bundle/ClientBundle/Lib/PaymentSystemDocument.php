<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib;


abstract class PaymentSystemDocument
{
    public static function name(): string
    {
        $name = explode('\\', static::class);

        return end($name);
    }
}