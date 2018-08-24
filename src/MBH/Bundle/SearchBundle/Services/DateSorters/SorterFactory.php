<?php


namespace MBH\Bundle\SearchBundle\Services\DateSorters;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;

class SorterFactory
{
    /**
     * @param string $sorterName
     * @return DateSorterInterface
     * @throws SearchQueryGeneratorException
     */
    public static function createSorter(string $sorterName): DateSorterInterface
    {
        switch ($sorterName) {
            case 'nearestFirst':
                return new NearestFirstSorter();
        }

        throw new SearchQueryGeneratorException('Can not find sorter to additional dates');
    }
}