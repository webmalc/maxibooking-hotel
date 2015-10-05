<?php

namespace MBH\Bundle\PackageBundle\Tests\Document;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PackageTest
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PackageTest extends WebTestCase
{
    public function testDiscountMoney()
    {
        $package = new Package();
        $package->setPrice(200);
        $this->assertEquals($package->getPrice(), 200) ;

        $package->setDiscount(10);
        $this->assertEquals($package->getPrice(), 180) ;

        $package->setIsPercentDiscount(false);
        $this->assertEquals($package->getPrice(), 190) ;
    }
}