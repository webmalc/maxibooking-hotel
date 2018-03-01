<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class OnlineCommonDataProvider implements OnlineDataProviderInterface
{

    /** @var SearchFactory */
    private $search;

    /** @var array */
    private $options;

    /** @var OnlineResultCreator */
    private $onlineResultCreator;

    /**
     * OnlineCommonDataProvider constructor.
     * @param SearchFactory $search
     * @param array $onlineOptions
     * @param OnlineResultCreator $creator
     */
    public function __construct(SearchFactory $search,  OnlineResultCreator $creator, array $onlineOptions)
    {
        $this->search = $search;
        $this->options = $onlineOptions;
        $this->onlineResultCreator = $creator;
    }


    public function search(OnlineSearchFormData $formData, SearchQuery $searchQuery): array
    {
        $result = [];
        $this->configureSearchQuery($searchQuery, $formData);
        $searchResults = $this->search->search($searchQuery);
        if (count($searchResults)) {
            foreach ($searchResults as $searchResult) {
//                $onlineInstance = $this->resultOnlineInstanceCreator($searchResult, $searchQuery);
//                $result->add($onlineInstance);
                $a = 'b';
            }
        }
        return $result;
    }

    public function getType(): string
    {
        return 'common';
    }

    private function configureSearchQuery(SearchQuery $searchQuery, OnlineSearchFormData $formData)
    {
        $searchQuery->setSave(true);
        if ($this->options['add_search_dates'] && $formData->isAddDates()) {
            $range = $this->options['add_search_dates'];
            $searchQuery->range = $range;
            $this->search->setAdditionalDates($range);
        }
        $this->search->setWithTariffs();

    }


}