<?php


namespace MBH\Bundle\SearchBundle\Lib\Events;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\EventDispatcher\Event;

class SearchEvent extends Event
{

    public const SEARCH_SYNC_START = 'search.sync.start';

    public const SEARCH_SYNC_END = 'search.sync.end';

    public const SEARCH_ASYNC_START = 'search.async.start';

    public const SEARCH_ASYNC_END = 'search.async.end';

    /** @var SearchConditions */
    private $searchConditions;

    /**
     * @return SearchConditions
     */
    public function getSearchConditions(): SearchConditions
    {
        return $this->searchConditions;
    }

    /**
     * @param SearchConditions $searchConditions
     * @return SearchEvent
     */
    public function setSearchConditions(SearchConditions $searchConditions): SearchEvent
    {
        $this->searchConditions = $searchConditions;

        return $this;
    }


}