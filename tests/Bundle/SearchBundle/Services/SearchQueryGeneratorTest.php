<?php


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;

class SearchQueryGeneratorTest extends WebTestCase
{
    public function testGenerate()
    {
        $generator = new SearchQueryGenerator();
    }
}