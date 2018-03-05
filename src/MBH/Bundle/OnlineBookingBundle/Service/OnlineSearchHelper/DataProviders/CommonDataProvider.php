<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters\OnlineCreatorInterface;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery\OnlineSearchQueryGenerator;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

/**
 * Class OnlineCommonDataProvider
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class CommonDataProvider implements DataProviderInterface
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


    /**
     * @param OnlineSearchFormData $formData
     * @return array
     */
    public function search(OnlineSearchFormData $formData): array
    {
        $results = [];
        $searchQuery = $this->queryGenerator->createSearchQuery($formData);
        $this->configureSearchQuery($searchQuery, $formData);
        $searchResults = $this->search->search($searchQuery);
        if (count($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $onlineInstance = $this->onlineResultCreator->create($searchResult, $searchQuery);
                $results[] = $onlineInstance;
            }
        }

        return $results;
    }


    /**
     * @param SearchQuery $searchQuery
     * @param OnlineSearchFormData $formData
     */
    private function configureSearchQuery(SearchQuery $searchQuery, OnlineSearchFormData $formData)
    {
        $searchQuery->setSave(true);
        $this->search->setWithTariffs();

    }

    public function getType(): string
    {
        return 'common';
    }


}