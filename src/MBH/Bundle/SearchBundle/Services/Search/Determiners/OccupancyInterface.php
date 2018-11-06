<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners;


/**
 * Interface OccupancyInterface
 * @package MBH\Bundle\SearchBundle\Services\Search\Determiners
 */
interface OccupancyInterface
{
    /**
     * @return int
     */
    public function getAdults(): int;

    /**
     * @return int
     */
    public function getChildren(): int;

    /**
     * @return int
     */
    public function getInfants(): int;

    /**
     * @return int[]
     */
    public function getChildrenAges(): array;
}