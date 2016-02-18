<?php

namespace MBH\Bundle\PackageBundle\Lib;

/**
 * Interface PayerInterface
 * @package MBH\Bundle\PackageBundle\Lib
 *

 */
interface PayerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getShortName();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPhone();
}