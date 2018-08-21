<?php
/**
 * Created by PhpStorm.
 * Date: 17.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Lib\AddressInterface;

interface AdvancedAddressInterface extends AddressInterface
{
    public function getCity(): string;

    public function getRegion(): string;

    public function getCountry(): string;

    public function getSellement(): string;
}