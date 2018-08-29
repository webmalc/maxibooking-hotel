<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Services\Search\CacheSearcher;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use Psr\Log\LoggerInterface;

class CacheWarmer
{

    public const MAX_CAPACITY = 6;

    public const MAX_ADULTS_RESTRICTIONS = 3;

    public const MAX_CHILDREN_RESTRICTION = 2;

    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /** @var LoggerInterface */
    private $logger;
    /**
     * @var SearchConditionsCreator
     */
    private $creator;
    /**
     * @var CacheSearcher
     */
    private $searcher;

    /**
     * CacheWarmer constructor.
     * @param SearchConditionsCreator $creator
     * @param SearchQueryGenerator $queryGenerator
     * @param CacheSearcher $searcher
     * @param LoggerInterface $logger
     */
    public function __construct(SearchConditionsCreator $creator, SearchQueryGenerator $queryGenerator, CacheSearcher $searcher, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->creator = $creator;
        $this->queryGenerator = $queryGenerator;
        $this->searcher = $searcher;
    }


    public function warmUp(\DateTime $date = null, \Closure $externalProgress = null, \Closure $internalProgress = null): void
    {
        $this->logger->info('Start cache warmUp');
        if (null === $date) {
            $date = new \DateTime('midnight');
        }
        $adultsChildrenCombinations = $this->getPlacesCombinations();
        $month = (int)$date->format('m');
        $year = (int)$date->format('Y');
        $begin = new \DateTime("8-${month}-${year} midnight");
        $end = new \DateTime("22-${month}-${year} midnight");
        $conditionsData = [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'additionalBegin' => 0,
            'additionalEnd' => 0,
            'isUseCache' => true,
        ];
        $externalExpected = \count($adultsChildrenCombinations);
        $externalCount = 0;
        foreach ($adultsChildrenCombinations as $combination) {
            $data = array_merge($conditionsData, $combination);
            try {
                if (null !== $externalProgress) {
                    $externalProgress($externalExpected, $externalCount++);
                }
                $this->logger->info('Start search with data ', $data);
                $conditions = $this->creator->createSearchConditions($data);
                $conditions->setId('warmerConditions');
                $queries = $this->queryGenerator->generate($conditions, false);
                $internalExpected = \count($queries);
                $internalCount = 0;
                foreach ($queries as $searchQuery) {
                    try {
                        if (null !== $internalProgress) {
                            $internalProgress($internalExpected, $internalCount++);
                        }
                        $this->searcher->search($searchQuery);
                    } catch (SearchResultComposerException|SharedFetcherException $e) {
                        $this->logger->error('Searcher Error '.$e->getMessage());
                    }
                }
                if (null !== $internalProgress) {
                    $internalProgress(0, 0, true);
                }
            } catch (SearchConditionException|SearchQueryGeneratorException $e) {
                $this->logger->error('Search Error '.$e->getMessage());
            }

        }

    }

    private function getPlacesCombinations(): array
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

        $result = [];
        foreach ($filteredCombinations as $combination) {
            $result[] = $this->generateChildrenAges($combination);
        }


        return array_merge(...$result);
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
            $inputArrays[] = range(SearchConditionsType::MIN_CHILDREN_AGE, SearchConditionsType::MAX_CHILDREN_AGE);
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