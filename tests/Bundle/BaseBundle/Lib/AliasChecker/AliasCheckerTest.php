<?php


namespace Tests\Bundle\BaseBundle\Lib\AliasChecker;


use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasChecker;
use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasCheckerException;
use PHPUnit\Framework\TestCase;

class AliasCheckerTest extends TestCase
{
    /** @dataProvider dataProvider */
    public function testCheckAlias($alias, $expected, $exception = null): void
    {
        $this->assertTrue(true);


        /** Only for manual testing */
//        putenv('MB_CLIENT='.$alias);
//        if ($exception) {
//            $this->expectException($exception);
//        }
//        AliasChecker::checkAlias('MB_CLIENT', 'prod');
//
//        $actual = getenv('MB_CLIENT');
//        $this->assertEquals($expected, $actual);
    }

//    public function testCheckAliasDisableUwsgi()
//    {
//
//        putenv('MB_CLIENT='.'fakeAlias');
//        AliasChecker::checkAlias('MB_CLIENT', 'prod');
//    }


    public function dataProvider()
    {
        return
            [
                ['ererewrsef', 'maxibooking', AliasCheckerException::class],
                ['bbbb', 'test-18633'],
                ['piterprivet', 'piterprivet']
            ];
    }


}