<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters;


interface ErrorFilterInterface
{
    public function loadFilter($result);

    public function saveFilter($result);
}