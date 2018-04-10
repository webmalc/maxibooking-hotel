<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 06.04.18
 * Time: 12:00
 */

namespace MBH\Bundle\BaseBundle\Service;


use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;

class Address
{
    private const IMPERIAL_LOCAL = 'en';

    /**
     * @var BillingApi
     */
    private $billing;

    /**
     * @var AddressInterface
     */
    private $entity;

    public function __construct(BillingApi $billingApi)
    {
        $this->billing = $billingApi;
    }

    private function rawImperialCity($local): array
    {
        $city = '';
        $region = '';
        $zipCode = '';

        if ($this->entity->getCityId() !== null) {
            $city = $this->billing->getCityById($this->entity->getCityId(), $local)->getName();
        }
        if ($this->entity->getRegionId() !== null) {
            $region = $this->billing->getRegionById($this->entity->getRegionId(), $local)->getName();
        }
        if ($this->entity->getZipCode() !== null) {
            $zipCode = $this->entity->getZipCode();
        }
        return [$city, $region, $zipCode];
    }

    private function rawImperialStreet(): array
    {
        $house = '';
        $street = '';

        if ($this->entity->getHouse() !== null) {
            $house = $this->entity->getHouse();
        }
        if ($this->entity->getStreet() !== null) {
            $street = $this->entity->getStreet();
        }

        return [$house, $street];
    }

    public function getImperialCityStr(AddressInterface $entity,$local = self::IMPERIAL_LOCAL): string
    {
        $this->entity = $entity;
        return implode(', ', array_filter($this->rawImperialCity($local), function ($val){
            return $val != '';
        }));
    }

    public function getImperialStreetStr(AddressInterface $entity): string
    {
        $this->entity = $entity;
        return implode(', ', array_filter($this->rawImperialStreet(), function ($val){
            return $val != '';
        }));
    }
}