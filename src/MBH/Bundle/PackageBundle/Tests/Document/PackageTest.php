<?php

namespace MBH\Bundle\PackageBundle\Tests\Document;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PackageTest
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PackageTest extends WebTestCase
{
    public function testGetPrice()
    {
        $package = new Package();
        $package->setPrice(200);
        $this->assertEquals(200, $package->getPrice()) ;

        $package->setIsPercentDiscount(true);
        $package->setDiscount(10);
        $this->assertEquals(20, $package->getDiscountMoney()) ;
        $this->assertEquals(180, $package->getPrice()) ;

        $package->setIsPercentDiscount(false);
        $this->assertEquals(190, $package->getPrice());

        $promotion = new Promotion();
        $promotion->setIsPercentDiscount(true);
        $promotion->setDiscount(10);
        $package->setPromotion($promotion);
        $this->assertEquals(170, $package->getPrice());

        $promotion->setIsPercentDiscount(false);
        $this->assertEquals(180, $package->getPrice());

        $package->setDiscount(0);
        $this->assertEquals(190, $package->getPrice());
        $promotion->setIsPercentDiscount(true);
        $this->assertEquals(180, $package->getPrice());
    }
}