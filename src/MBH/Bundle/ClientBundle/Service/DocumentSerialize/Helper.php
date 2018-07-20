<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Document\Organization as OrganizationBase;
use MBH\Bundle\PackageBundle\Document\Tourist as TouristBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Helper
{
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $obj
     * @return Mortal|Organization
     */
    public function payerInstance($obj)
    {
        if ($obj instanceof TouristBase) {
            return $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal')->newInstance($obj);
        } elseif ($obj instanceof OrganizationBase) {
            return $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization')->newInstance($obj);
        } else {
            return null;
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function methodsOfEntity(): array
    {
        $c = $this->container;
        $hotel = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Hotel');
        $hotelOrganization = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization');
        $mortal = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal');
        $payerOrganization = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization');
        $order = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order');
        $user = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\User');
        $package = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Package');
        $cashDocument = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\CashDocument');
        $serviceGroup = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\ServiceGroup');
        $service = $c->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Service');

        return [
            'common' => [
                'hotel'        => $hotel->methods(),
                'organization' => $hotelOrganization->methods(),
                'payer'        => [
                    'mortal' => $mortal->methods(),
                    'organ'  => $payerOrganization->methods(),
                ],
                'order'        => $order->methods(),
                'user'         => $user->methods(),
                'package'      => $package->methods(),
            ],
            'table'  => [
                'cashDocument'    => [
                    'methods' => $cashDocument->methods(),
                    'source'  => 'order.allCashDocuments',
                ],
                'tourist'         => [
                    'methods' => $mortal->methods(),
                    'source'  => 'package.allTourists',
                ],
                'servicesByGroup' => [
                    'methods' => $serviceGroup->methods(),
                    'source'  => 'order.allServicesByGroup',
                ],
                'services'        => [
                    'methods' => $service->methods(),
                    'source'  => 'order.allServices',
                ],
            ],

        ];
    }

    /**
     * @param $value
     * @return string
     */
    public static function numFormat($value): string
    {
        return number_format($value, 2);
    }
}