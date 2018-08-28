<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache;


use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use MBH\Bundle\SearchBundle\Services\Search\Search;
use Monolog\Logger;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class CacheWarmerTest extends SearchWebTestCase
{
    public function testWarmUp()
    {
        $search = $this->createMock(Search::class);
        $search->expects($this->exactly(860))->method('searchSync')->with($this->isType('array'));
        $logger = $this->createMock(Logger::class);
        $warmer = new CacheWarmer($search, $logger);
        $warmer->warmUp();
    }

}