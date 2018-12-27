<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineSearchAdapter;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters\OnlineCreatorInterface;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery\OnlineSearchQueryGenerator;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class AdditionalDataProvider implements DataProviderInterface
{

    /** @var OnlineSearchAdapter */
    private $search;

    /** @var array */
    private $onlineOptions;

    /** @var OnlineCreatorInterface */
    private $onlineResultCreator;

    /** @var OnlineSearchQueryGenerator */
    private $queryGenerator;

    /**
     * OnlineCommonDataProvider constructor.
     * @param OnlineSearchAdapter $search
     * @param OnlineCreatorInterface $creator
     * @param OnlineSearchQueryGenerator $queryGenerator
     * @param array $onlineOptions
     */
    public function __construct(
        OnlineSearchAdapter $search,
        OnlineCreatorInterface $creator,
        OnlineSearchQueryGenerator $queryGenerator,
        array $onlineOptions
    ) {
        $this->search = $search;
        $this->onlineOptions = $onlineOptions;
        $this->onlineResultCreator = $creator;
        $this->queryGenerator = $queryGenerator;
    }

    public function search(OnlineSearchFormData $formData): array
    {
        $results = [];
        $searchQuery = $this->queryGenerator->createSearchQuery($formData);
        $range = $this->onlineOptions['add_search_dates'];
        $searchQuery->range = $range;
//        $this->search->setAdditionalDates($range);
//        $this->search->setWithTariffs();
        $searchResults = $this->search->search($searchQuery);
        $searchResults = $this->separateAdditional($searchResults, $formData->getBegin(), $formData->getEnd());

        if (count($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $onlineInstance = $this->onlineResultCreator->create($searchResult, $searchQuery);
                if ($onlineInstance) {
                    $results[] = $onlineInstance;
                }

            }
        }

        return $results;
    }

    private function separateAdditional(array $results, \DateTime $begin, \DateTime $end)
    {
        $separatedResults = [];
        /** Filter first */
        foreach ($results as $result) {
            $roomType = $result['roomType'];
            $searchResults = $result['results'];
            /** Фильтруем точные даты */
            $searchResults = array_filter(
                $searchResults,
                function (SearchResult $searchResult) use ($begin, $end) {
                    return !($searchResult->getBegin() == $begin && $searchResult->getEnd() == $end);
                }
            );
            $grouped = $this->groupByDay($searchResults);
            if (count($grouped)) {
                foreach ($grouped as $value) {
                    $separatedResults[] = [
                        'roomType' => $roomType,
                        'results' => $value,
                    ];
                }
            }
        }

        return $separatedResults;
    }

    private function groupByDay(array $results): array
    {
        $groups = [];
        foreach ($results as $keyNeedleInstance => $searchNeedleInstance) {
            /** @var SearchResult $searchNeedleInstance */
            $needle = $this->getDateHash($searchNeedleInstance);
            foreach ($results as $searchKey => $searchInstance) {
                /** @var SearchResult $searchInstance */
                $hayStack = $this->getDateHash($searchInstance);
                if ($needle === $hayStack) {
                    $groups[$needle][$searchKey] = $searchInstance;
                }
            }
        }

        return $groups;
    }

    private function getDateHash(SearchResult $searchResult): string
    {
        return $searchResult->getBegin()->format('dmY').$searchResult->getEnd()->format('dmY');
    }

    public function getType(): string
    {
        return 'additional';
    }

}