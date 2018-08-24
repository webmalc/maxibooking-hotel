<?php


namespace MBH\Bundle\SearchBundle\Services\DateSorters;


interface DateSorterInterface
{
    public function sort(\DateTime $begin, \DateTime $end, array $dates): array;
}