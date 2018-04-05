<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 04.04.18
 * Time: 16:35
 */

namespace MBH\Bundle\BaseBundle\Document\Interfaces;


interface InterfaceAddressCity
{
    public function getCity();

    public function getRegionId();

    public function getZipCode();
}