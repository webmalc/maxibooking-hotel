<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery\OnlineSearchQueryGenerator;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

/**
 * Class OnlineCommonDataProvider
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class OnlineCommonDataProvider implements OnlineDataProviderInterface
{

    const TYPE = 'common';

    const ADDITIONALTYPE = '';

    /** @var SearchFactory */
    private $search;

    /** @var array */
    private $onlineOptions;

    /** @var OnlineResultCreator */
    private $onlineResultCreator;

    /** @var OnlineSearchQueryGenerator */
    private $queryGenerator;

    /**
     * OnlineCommonDataProvider constructor.
     * @param SearchFactory $search
     * @param array $onlineOptions
     * @param OnlineResultCreator $creator
     */
    public function __construct(SearchFactory $search,  OnlineResultCreator $creator, OnlineSearchQueryGenerator $queryGenerator, array $onlineOptions)
    {
        $this->search = $search;
        $this->onlineOptions = $onlineOptions;
        $this->onlineResultCreator = $creator;
        $this->queryGenerator = $queryGenerator;
    }


    /**
     * @param OnlineSearchFormData $formData
     * @return array
     * @throws \MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException
     */
    public function search(OnlineSearchFormData $formData): array
    {
        $results = [];
        $searchQuery = $this->queryGenerator->createSearchQuery($formData);
        $this->configureSearchQuery($searchQuery, $formData);
        $searchResults = $this->search->search($searchQuery);
        if (count($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $onlineInstance = $this->onlineResultCreator->createCommon($searchResult, $searchQuery, $this->getType());
                $results[] = $onlineInstance;
            }
        }

        $results = $this->separateByAdditionalDays($searchQuery, $results);

        return $results;
    }


    /**
     * @param SearchQuery $searchQuery
     * @param OnlineSearchFormData $formData
     */
    private function configureSearchQuery(SearchQuery $searchQuery, OnlineSearchFormData $formData)
    {
        $searchQuery->setSave(true);
        if ($this->onlineOptions['add_search_dates'] && $formData->isAddDates()) {
            $range = $this->onlineOptions['add_search_dates'];
            $searchQuery->range = $range;
            $this->search->setAdditionalDates($range);
        }
        $this->search->setWithTariffs();

    }

    /**
     * Divide results to match and additional dates
     * @param SearchQuery $searchQuery
     * @param array $results
     * @return array
     */
    private function separateByAdditionalDays(SearchQuery $searchQuery, array $results): array
    {
        //TODO: Отключил пока сортировку с допдатами допдаты. Тут нужно будет переделывать немного.
//        $result = [];
//        foreach ($results as $resultInstance) {
//            /** @var OnlineResultInstance $resultInstance */
//            $groups = [];
//
//            foreach ($resultInstance->getResults() as $keyNeedleInstance => $searchNeedleInstance) {
//                /** @var SearchResult $searchNeedleInstance */
//                $needle = $searchNeedleInstance->getBegin()->format('dmY').$searchNeedleInstance->getEnd()->format(
//                        'dmY'
//                    );
//                foreach ($resultInstance->getResults() as $searchKey => $searchInstance) {
//                    /** @var SearchResult $searchInstance */
//                    $hayStack = $searchInstance->getBegin()->format('dmY').$searchInstance->getEnd()->format('dmY');
//                    if ($needle == $hayStack) {
//                        $groups[$needle][$searchKey] = $searchInstance;
//                    }
//                }
//            }
//            foreach ($groups as $group) {
//                $instance = $this->createOnlineResultInstance($resultInstance->getRoomType(), array_values($group), $searchQuery);
//                //Грязных хак для показа только результатов с доп датами
//                if ($this->originalFormData->isAddDates() && $instance->getType() === 'common') {
//                    continue;
//                }
//                $result[] = $instance;
//            }
//        }
//
//        usort(
//            $result,
//            function ($resA, $resB) {
//                /** @var OnlineResultInstance $resA */
//                /** @var OnlineResultInstance $resB */
//                $priceA = $resA->getResults()->first()->getPrices();
//                $priceB = $resB->getResults()->first()->getPrices();
//
//                return reset($priceA) <=> reset($priceB);
//            }
//        );
//
//        return $result;
        return $results;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }



}