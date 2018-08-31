<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


trait TraitAddress
{
    public function __construct(ContainerInterface $container = null, BillingApi $billingApi = null)
    {
        parent::__construct($container);
        $this->billing = $billingApi;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function newInstance($entity)
    {
        $this->instanseOf($entity);
        $this->entity = $entity;
        $this->setAddress($this->entity);

        return $this;
    }

    /**
     * @var BillingApi
     */
    protected $billing;

    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @return string
     */
    public function getCity(): string
    {
        $city = '';

        if ($this->address !== null && !empty($id = $this->address->getCityId() !== null)) {
            $city = $this->billing->getCityById($id)->getName();
        }

        return $city;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        $region = '';

        if ($this->address !== null && !empty($id = $this->address->getRegionId())) {
            $region = $this->billing->getRegionById($id)->getName();
        }

        return $region;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        $country = '';

        if ($this->address !== null && !empty($id = $this->address->getCountryTld())) {
            $country = $this->billing->getCountryByTld($id)->getName();
        }

        return $country;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
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

    /**
     * @param AddressInterface|null $address
     */
    protected function setAddress(?AddressInterface $address): void
    {
        $this->address = $address;
    }

    public function getName(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getCountryTld(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getRegionId(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->returnValue(__METHOD__);
    }

    /**
     * @return string
     */
    public function getSellement(): string
    {
        /** TODO надо посмотреть это встречается */
        if (!method_exists($this->address, 'getSellement')) {
            return '';
        }

        return $this->returnValue(__METHOD__);
    }

    /**
     * @param $method
     * @return string
     */
    private function returnValue($method): string
    {
        $temp = explode('::', $method);
        $method = end($temp);

        return $this->address !== null && $this->address->$method() !== null ? $this->address->$method() : '';
    }
}