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

    public function testGetPricesByDateByPrice()
    {
        $package = new Package();
        $package->setPricesByDate([
            '06_10_2015' => 2000,
            '07_10_2015' => 2000,
            '08_10_2015' => 2000,
            '09_10_2015' => 2000,
            '10_10_2015' => 2000,
            '11_10_2015' => 2000,
            '12_10_2015' => 2000,
            '13_10_2015' => 2000,
            '14_10_2015' => 2000,
            '15_10_2015' => 2000,
        ]);
        $this->assertEquals([
            '06_10_2015 - 16_10_2015' => ['price' => 2000, 'nights' => 10]
        ], $package->getPricesByDateByPrice());


        $package->setPricesByDate([
            '10_01_2016' => 4400,
            '11_01_2016' => 3800,
            '12_01_2016' => 3800,
            '13_01_2016' => 3800,
            '14_01_2016' => 3800,
            '15_01_2016' => 3800,
        ]);
        $this->assertEquals([
            '10_01_2016 - 11_01_2016' => ['price' => 4400 , 'nights' => 1],
            '11_01_2016 - 16_01_2016' => ['price' => 3800 , 'nights' => 5]
        ], $package->getPricesByDateByPrice());

        $package->setPricesByDate([
            '31_12_2015' => 6000,
            '01_01_2016' => 6000,
            '02_01_2016' => 6000,
            '03_01_2016' => 6000,
            '04_01_2016' => 6000,
        ]);
        $this->assertEquals([
            '31_12_2015 - 05_01_2016' => ['price' => 6000 , 'nights' => 5]
        ], $package->getPricesByDateByPrice());
    }
}