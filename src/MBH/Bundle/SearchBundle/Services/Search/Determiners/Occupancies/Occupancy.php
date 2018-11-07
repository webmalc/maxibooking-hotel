<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class Occupancy implements OccupancyInterface
{
    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var int */
    private $infants;


    /** @var int[] */
    private $childrenAges;


    public function __construct(int $adults, int $children = 0, int $infants = 0, array $childrenAges = [])
    {

        $this->adults = $adults;
        $this->children = $children;
        $this->infants = $infants;
        $this->childrenAges = $childrenAges;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * @return int[]
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    public function getInfants(): int
    {
        return $this->infants;
    }


    public static function createInstanceBySearchQuery(SearchQuery $searchQuery): Occupancy
    {
        $instance = new static(
            $searchQuery->getAdults(),
            $searchQuery->getChildren() ?? 0,
            0,
            $searchQuery->getChildrenAges() ?? []
        );

        return $instance;
    }
}