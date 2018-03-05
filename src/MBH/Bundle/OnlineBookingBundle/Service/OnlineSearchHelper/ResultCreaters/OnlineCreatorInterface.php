<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters;


use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

interface OnlineCreatorInterface
{
    public function create($searchResult, SearchQuery $searchQuery): ?OnlineResultInstance;
}