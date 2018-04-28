<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;

use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;


trait AddressSerialize
{
    /**
     * @var BillingApi
     */
    protected $billing;

    /**
     * @var AddressInterface
     */
    private $address;

    public function setBilling(BillingApi $billingApi): void
    {
        $this->billing = $billingApi;
    }

    public function getCity(): string
    {
        $city = '';

        if ($this->address !== null && !empty($id = $this->address->getCityId() !== null)) {
            $city = $this->billing->getCityById($id)->getName();
        }
        return $city;
    }

    public function getRegion(): string
    {
        $region = '';

        if ($this->address !== null && !empty($id = $this->address->getRegionId())) {
            $region = $this->billing->getRegionById($id)->getName();
        }
        return $region;
    }

    public function getCountry(): string
    {
        $country = '';

        if ($this->address !== null && !empty($id = $this->address->getCountryTld())) {
            $country = $this->billing->getCountryByTld($id)->getName();
        }
        return $country;
    }

    public function getZipCode(): string
    {
        return $this->returnValue(__METHOD__);
    }

    public function getStreet(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getCorpus(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getFlat(): string
    {
        return $this->returnValue(__METHOD__);
    }

    protected function setAddress(?AddressInterface $address): void
    {
        $this->address = $address;
    }

    private function returnValue($method): string
    {
        $temp = explode('::',$method);
        $method = end($temp);
        return $this->address !== null && $this->address->$method() !== null ? $this->address->$method() : '';
    }
}