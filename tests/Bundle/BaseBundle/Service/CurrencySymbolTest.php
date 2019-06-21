<?php
/**
 * Date: 21.06.19
 */

namespace Tests\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\CurrencySymbol;

class CurrencySymbolTest extends UnitTestCase
{

    /**
     * @var CurrencySymbol
     */
    private static $currencySymbol;

    public static function setUpBeforeClass()
    {
        self::$currencySymbol = self::getContainerStat()->get(CurrencySymbol::class);
    }

    public function getDataForSymbolWithPrice(): iterable
    {
        $templateSymbol = '<span class="currency-symbol currency-symbol-first">$</span>';
        $templatePrice = '<%1$s %2$sclass="price-wrapper">200</%1$s>';

        yield 'default' => [
            'template' => sprintf($templateSymbol . $templatePrice, 'span',''),
            'param'    => ['wrapperId' => null, 'wrapperTag' => null]
        ];

        $wrapperId = 'test-wrapper';
        $tempWrapperId = sprintf('id="%s" ', $wrapperId);

        yield 'with wrapper id' => [
            'template' => sprintf($templateSymbol . $templatePrice, 'span', $tempWrapperId),
            'param'    => ['wrapperId' => $wrapperId, 'wrapperTag' => null]
        ];

        $wrapperTag = 'div';

        yield 'with wrapper tag' => [
            'template' => sprintf($templateSymbol . $templatePrice, $wrapperTag, ''),
            'param'    => ['wrapperId' => null, 'wrapperTag' => $wrapperTag]
        ];
    }

    /**
     * @dataProvider getDataForSymbolWithPrice
     */
    public function testSymbolWithPrice($expected, $param)
    {
        $this->assertEquals(
            $expected,
            $this->service()->symbolWithPrice((string)200, $param['wrapperId'], $param['wrapperTag'])
        );
    }

    private function service(): CurrencySymbol
    {
        return self::$currencySymbol;
    }
}
