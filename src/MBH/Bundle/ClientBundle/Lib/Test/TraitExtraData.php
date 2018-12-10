<?php
/**
 * Created by PhpStorm.
 * Date: 28.09.18
 */

namespace MBH\Bundle\ClientBundle\Lib\Test;


use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;

trait TraitExtraData
{
    /**
     * @var ExtraData
     */
    private static $extraData;


    /**
     * @return ExtraData
     */
    private function getExtraData(): ExtraData
    {
        if (self::$extraData === null) {
            self::$extraData = $this->getContainer()->get('mbh.payment_extra_data');
        }

        return self::$extraData;
    }
}