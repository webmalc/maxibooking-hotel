<?php
/**
 * Date: 21.06.19
 */

namespace Tests\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\Currency;

class CurrencyTest extends UnitTestCase
{
    private const DEFAULT_CURRENCY_DATA = [
        'text'  => '$',
        'small' => '¢',
        'icon'  => 'fa fa-usd',
        'side'  => 'left',
    ];

    /**
     * @var Currency
     */
    private static $currency;

    public static function setUpBeforeClass()
    {
        self::$currency = self::getContainerStat()->get('mbh.currency');
    }

    public function setUp()
    {
    }

    public function testInfo()
    {
        $this->assertEquals($this->defaultCurrency(), $this->currencyService()->info());
    }

    public function testInfoForMbSite()
    {
        $this->assertEquals(
            ['symbol' => $this->defaultCurrency()['text'], 'side' => $this->defaultCurrency()['side']],
            $this->currencyService()->info(true)
        );
    }

    private function currencyService(): Currency
    {
        return self::$currency;
    }

    private function defaultCurrency(): array
    {
        return self::DEFAULT_CURRENCY_DATA;
    }
}
