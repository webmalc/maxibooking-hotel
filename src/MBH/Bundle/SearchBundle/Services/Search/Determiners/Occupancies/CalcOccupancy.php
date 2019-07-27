<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Services\Search\Determiners\CalcOccupancyInterface;

class CalcOccupancy implements CalcOccupancyInterface
{
    private $adults;

    private $children;

    private $addAdults;

    private $addChildren;

    /**
     * CalcOccupancy constructor.
     * @param $adults
     * @param $children
     * @param $addAdults
     * @param $addChildren
     */
    public function __construct($adults, $children, $addAdults, $addChildren)
    {
        $this->adults = $adults;
        $this->children = $children;
        $this->addAdults = $addAdults;
        $this->addChildren = $addChildren;
    }


    public function getMainAdults(): int
    {
        return $this->adults;
    }

    public function getMainChildren(): int
    {
        return $this->children;
    }

    public function getAddAdults(): int
    {
        return $this->addAdults;
    }

    public function getAddChildren(): int
    {
        return $this->addChildren;
    }

}