<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


interface SearchNecessarilyInterface
{
    public function isSearchNecessarily(): bool;
}