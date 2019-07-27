<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


interface SearchConditionsInterface
{
    public function getSearchHash(): string;

    public function getAdditionalResultsLimit();

    public function getErrorLevel(): int;
}