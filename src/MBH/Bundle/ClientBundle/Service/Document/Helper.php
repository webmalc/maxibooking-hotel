<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;

class Helper
{
    public static function payerInstance($obj)
    {
        if ($obj instanceof Tourist) {
            return new MortalSerialize($obj);
        } elseif ($obj instanceof Organization) {
            return new OrganizationSerialize($obj);
        } else {
            throw new \LogicException('can not be');
        }
    }

    public static function methodsOfEntity(): array
    {
        return [
            'hotel'     => HotelSerialize::methods(),
            'payer'     => [
                'mortal' => MortalSerialize::methods(),
                'organ'  => OrganizationSerialize::methods(),
            ],
            'order'     => OrderSerialize::methods(),
            'user'      => UserSerialize::methods(),
            'package'   => PackageSerialize::methods(),
            'cashDocument' => CashDocumentSerialize::methods(),
        ];
    }

    public static function numFormat($value):string
    {
        return number_format($value, 2);
    }
}