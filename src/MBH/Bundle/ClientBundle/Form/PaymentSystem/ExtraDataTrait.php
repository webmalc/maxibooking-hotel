<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;

trait ExtraDataTrait
{
    /** @var ExtraData  */
    private $extraData;

    public function __construct(ExtraData $extraData)
    {
        $this->extraData = $extraData;
    }
}