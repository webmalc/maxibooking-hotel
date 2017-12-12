<?php namespace MBH\Bundle\PackageBundle\Lib;

/**
 * Interface AddressInterface
 * @package MBH\Bundle\PackageBundle\Lib
 */
interface AddressInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCountryTld();

    /**
     * @return string
     */
    public function getRegionId();

    /**
     * @return string
     */
    public function getCityId();

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @return string
     */
    public function getHouse();

    /**
     * @return string
     */
    public function getCorpus();

    /**
     * @return string
     */
    public function getFlat();
}