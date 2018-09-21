<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations;


abstract class AbstractCombinations implements CombinationInterface
{

    public const MAX_CAPACITY = 6;

    public const MAX_ADULTS_RESTRICTIONS = 3;

    public const MAX_CHILDREN_RESTRICTION = 3;

    /** @var array */
    private $tariffIds = [];

    public function addTariffId(string $id): self
    {
        $this->tariffIds[] = $id;

        return $this;
    }

    public function getTariffIds(): array
    {
        return $this->tariffIds;
    }


    protected function getPlacesCombinations(): array
    {
        $combinations = [];
        foreach (range(1, self::MAX_CAPACITY) as $adultPlace) {
            foreach (range(0, self::MAX_CAPACITY - $adultPlace) as $childrenPlace) {
                $combinations[] = [
                    'adults' => $adultPlace,
                    'children' => $childrenPlace,
                ];
            }
        }

        $filteredCombinations = array_filter(
            $combinations,
            function ($variant) {
                return $variant['children'] <= self::MAX_CHILDREN_RESTRICTION && $variant['adults'] <= self::MAX_ADULTS_RESTRICTIONS;
            }
        );

        return $filteredCombinations;
    }
}