<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\TariffLimitCheckerException;
use MBH\Bundle\SearchBundle\Services\Search\TariffLimitChecker;

class TariffLimitCheckerTest extends WebTestCase
{
    public function testCkeckSuccess()
    {
        $tariffBegin = new \DateTime("yesterday midnight");
        $tariffEnd = new \DateTime("midnight + 5 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new TariffLimitChecker();

        $this->assertNull($checker->check($tariff));


    }

    public function testCheckFailedBegin()
    {
        $tariffBegin = new \DateTime("tomorrow midnight");
        $tariffEnd = new \DateTime("midnight + 5 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new TariffLimitChecker();
        $this->expectException(TariffLimitCheckerException::class);
        $checker->check($tariff);
    }

    public function testCheckFailedEnd()
    {
        $tariffBegin = new \DateTime('midnight -5 days');
        $tariffEnd = new \DateTime("midnight -1 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new TariffLimitChecker();
        $this->expectException(TariffLimitCheckerException::class);
        $checker->check($tariff);
    }
}