<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;

class Payer
{
    public static function instance($obj)
    {
        if ($obj instanceof Tourist){
            return new MortalSerialize($obj);
        } elseif ($obj instanceof Organization) {
            return new OrganizationSerialize($obj);
        } else {
            throw new \LogicException('can not be');
        }
    }
}