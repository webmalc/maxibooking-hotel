<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


interface AsyncQueryGroupInterface
{
    public function unsetConditions(): void;
}