<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups\Traits;

use MBH\Bundle\SearchBundle\Lib\SearchQuery;

/**
 * Trait UnsetConditionTrait
 * @package MBH\Bundle\SearchBundle\Services\QueryGroups\Traits
 * @method SearchQuery[] getSearchQueries
 */
trait UnsetConditionTrait
{
    public function unsetConditions(): void
    {
        foreach ($this->getSearchQueries() as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $searchQuery->unsetConditions();
        }
    }
}