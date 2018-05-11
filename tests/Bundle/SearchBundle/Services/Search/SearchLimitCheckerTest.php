<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException;
use MBH\Bundle\SearchBundle\Services\Search\SearchLimitChecker;

class SearchLimitCheckerTest extends WebTestCase
{
    /** TODO: Рефакторить тест и зафигачить все красиво. */
    public function testCheckTariffConditions()
    {
        $tariffBegin = new \DateTime("yesterday midnight");
        $tariffEnd = new \DateTime("midnight + 5 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new SearchLimitChecker();

        $this->assertNull($checker->checkDateLimit($tariff));
    }

    public function testCheckTariffNoTariffConditions()
    {
        $tariff = new Tariff();
        $checker = new SearchLimitChecker();
        $this->assertNull($checker->checkDateLimit($tariff));
    }

    public function testCheckTariffConditionsFailedBegin()
    {
        $tariffBegin = new \DateTime("tomorrow midnight");
        $tariffEnd = new \DateTime("midnight + 5 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new SearchLimitChecker();
        $this->expectException(SearchLimitCheckerException::class);
        $checker->checkDateLimit($tariff);
    }

    public function testCheckTariffConditionsFailedEnd()
    {
        $tariffBegin = new \DateTime('midnight -5 days');
        $tariffEnd = new \DateTime("midnight -1 day");
        $tariff = new Tariff();
        $tariff->setBegin($tariffBegin)->setEnd($tariffEnd);
        $checker = new SearchLimitChecker();
        $this->expectException(SearchLimitCheckerException::class);
        $checker->checkDateLimit($tariff);
    }

}