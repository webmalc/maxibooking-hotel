<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations;


class ChildrenAgesCombination extends AbstractCombinations
{
    public function getCombinations(): array
    {
        $combinations = $this->getPlacesCombinations();
        $childrenAgesResult = [];
        foreach ($combinations as $combination) {
            $childrenAgesResult[] = $this->generateChildrenAges($combination);
        }
        $combinations = array_merge(...$childrenAgesResult);

        return $combinations;
    }

    private function generateChildrenAges(array $combination): array
    {
        $children = $combination['children'];
        if (0 === $children) {
            $combination['childrenAges'] = [];

            return [$combination];
        }

        $inputArrays = [];

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach (range(1, $children) as $foo) {
//            $inputArrays[] = range(SearchConditionsType::MIN_CHILDREN_AGE, SearchConditionsType::MAX_CHILDREN_AGE);
            $inputArrays[] = [3, 14];

        }



        $agesCombinations = $this->getAllCombinations($inputArrays);
        $result = [];
        foreach ($agesCombinations as $agesCombination) {
            $result[] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'childrenAges' => $agesCombination,
            ];
        }

        return $result;
    }

    /** @url https://gist.github.com/cecilemuller/4688876 */
    private function getAllCombinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_replace($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }

}