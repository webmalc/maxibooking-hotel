<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Organization as OrganizationBase;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist as TouristBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Helper
{
    private const PACKAGE_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Package::class;
    private const ORDER_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order::class;
    private const HOTEL_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Hotel::class;
    private const HOTEL_ORGANIZATION_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization::class;
    private const USER_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\User::class;
    private const MORTAL_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal::class;
    private const CASH_DOCUMENT_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\CashDocument::class;
    private const SERVICE_GROUP_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\ServiceGroup::class;
    private const SERVICE_DECORATOR = \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Service::class;

    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function entityDecoratorInstance($obj): Common
    {
        if ($obj instanceof \MBH\Bundle\PackageBundle\Document\Package) {
            $classNameDecorator = self::PACKAGE_DECORATOR;
        } elseif ($obj instanceof \MBH\Bundle\PackageBundle\Document\Order) {
            $classNameDecorator = self::ORDER_DECORATOR;
        } elseif ($obj instanceof \MBH\Bundle\UserBundle\Document\User) {
            $classNameDecorator = self::USER_DECORATOR;
        } elseif ($obj instanceof \MBH\Bundle\PackageBundle\Document\Organization) {
            $classNameDecorator = self::HOTEL_ORGANIZATION_DECORATOR;
        } elseif ($obj instanceof \MBH\Bundle\HotelBundle\Document\Hotel) {
            $classNameDecorator = self::HOTEL_DECORATOR;
        } elseif ($obj instanceof TouristBase) {
            $classNameDecorator = self::MORTAL_DECORATOR;
        } elseif ($obj instanceof \MBH\Bundle\CashBundle\Document\CashDocument) {
            $classNameDecorator = self::CASH_DOCUMENT_DECORATOR;
        } elseif ($obj instanceof PackageServiceGroupByService) {
            $classNameDecorator = self::SERVICE_GROUP_DECORATOR;
        } elseif ($obj instanceof PackageService) {
            $classNameDecorator = self::SERVICE_DECORATOR;
        }

        if (!isset($classNameDecorator)) {
            throw new \RuntimeException(
                sprintf('Unknown class for factory of decorators: %s', get_class($obj))
            );
        }

        return $this->container->get($classNameDecorator)->newInstance($obj);
    }

    /**
     * @param $obj
     * @return Mortal|Organization
     */
    public function payerInstance($obj)
    {
        if ($obj instanceof TouristBase) {
            return $this->container
                ->get(self::MORTAL_DECORATOR)->newInstance($obj);
        } elseif ($obj instanceof OrganizationBase) {
            return $this->container
                ->get(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization::class)->newInstance($obj);
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
        $hotel = $c->get(self::HOTEL_DECORATOR);
        $hotelOrganization = $c->get(self::HOTEL_ORGANIZATION_DECORATOR);
        $mortal = $c->get(self::MORTAL_DECORATOR);
        $payerOrganization = $c->get(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization::class);
        $order = $c->get(self::ORDER_DECORATOR);
        $user = $c->get(self::USER_DECORATOR);
        $package = $c->get(self::PACKAGE_DECORATOR);
        $cashDocument = $c->get(self::CASH_DOCUMENT_DECORATOR);
        $serviceGroup = $c->get(self::SERVICE_GROUP_DECORATOR);
        $service = $c->get(self::SERVICE_DECORATOR);

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