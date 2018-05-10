<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Helper
{
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function payerInstance($obj)
    {
        if ($obj instanceof Tourist) {
            return $this->container->get('MBH\Bundle\ClientBundle\Service\Document\MortalSerialize')->newInstance($obj);
        } elseif ($obj instanceof Organization) {
            return $this->container->get('MBH\Bundle\ClientBundle\Service\Document\OrganizationSerialize')->newInstance($obj);
        } else {
            throw new \LogicException('can not be');
        }
    }

    public static function methodsOfEntity(): array
    {
        return [
            'common' => [
                'hotel'   => HotelSerialize::methods(),
                'payer'   => [
                    'mortal' => MortalSerialize::methods(),
                    'organ'  => OrganizationSerialize::methods(),
                ],
                'order'   => OrderSerialize::methods(),
                'user'    => UserSerialize::methods(),
                'package' => PackageSerialize::methods(),
            ],
            'table'  => [
                'cashDocument' => CashDocumentSerialize::methods(),
                'tourist'      => MortalSerialize::methods(),
            ],

        ];
    }

    public static function numFormat($value): string
    {
        return number_format($value, 2);
    }
}