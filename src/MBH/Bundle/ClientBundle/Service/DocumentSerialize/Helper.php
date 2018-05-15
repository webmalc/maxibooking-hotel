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
            throw new \LogicException('can not be');
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function methodsOfEntity(): array
    {
        return [
            'common' => [
                'hotel'   => Hotel::methods(),
                'payer'   => [
                    'mortal' => Mortal::methods(),
                    'organ'  => Organization::methods(),
                ],
                'order'   => Order::methods(),
                'user'    => User::methods(),
                'package' => Package::methods(),
            ],
            'table'  => [
                'cashDocument' => [
                    'methods' => CashDocument::methods(),
                    'source'  => 'order.allCashDocuments',
                ],
                'tourist'      => [
                    'methods' => Mortal::methods(),
                    'source'  => 'package.allTourists',
                ],
                'serviceGroup' => [
                    'methods' => ServiceGroup::methods(),
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