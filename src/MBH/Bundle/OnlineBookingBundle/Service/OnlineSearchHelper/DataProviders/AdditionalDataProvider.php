<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters\OnlineCreatorInterface;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery\OnlineSearchQueryGenerator;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class AdditionalDataProvider implements DataProviderInterface
{

    /** @var SearchFactory */
    private $search;

    /** @var array */
    private $onlineOptions;

    /** @var OnlineCreatorInterface  */
    private $onlineResultCreator;

    /** @var OnlineSearchQueryGenerator */
    private $queryGenerator;

    /**
     * OnlineCommonDataProvider constructor.
     * @param SearchFactory $search
     * @param OnlineCreatorInterface $creator
     * @param OnlineSearchQueryGenerator $queryGenerator
     * @param array $onlineOptions
     */
    public function __construct(SearchFactory $search,  OnlineCreatorInterface $creator, OnlineSearchQueryGenerator $queryGenerator, array $onlineOptions)
    {
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
        $this->search->setAdditionalDates($range);
        $searchResults = $this->search->search($searchQuery);

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

    public function getType(): string
    {
        return 'additional';
    }

}