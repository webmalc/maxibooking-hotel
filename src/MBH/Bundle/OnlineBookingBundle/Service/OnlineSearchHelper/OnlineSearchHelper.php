<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

class OnlineSearchHelper
{

    /** @var array */
    private $options;

    /** @var  \SplObjectStorage */
    private $dataProviders;
    /** @var  Helper */
    private $helper;

    /** @var OnlineDataProviderWrapperInterface */
    private $additionalProvider;

    /**
     * OnlineSearchHelper constructor.
     * @param array $options
     * @param Helper $helper
     */
    public function __construct(array $options, Helper $helper)
    {
        $this->options = $options;
        $this->dataProviders = new \SplObjectStorage();
        $this->helper = $helper;
    }

    public function addDataProvider(OnlineDataProviderWrapperInterface $dataProvider)
    {
        $this->dataProviders->attach($dataProvider);
    }

    public function setAdditionalProvider(OnlineDataProviderWrapperInterface $dataProvider)
    {
        $this->additionalProvider = $dataProvider;
    }

    /**
     * @param OnlineSearchFormData $formInstance
     * @return array
     * TODO: Очень костыльно получилось с доп датами. В идеале рефакторить и тут.
     */
    public function getResults(OnlineSearchFormData $formInstance)
    {
        $results = [];
        if (!$this->isAdditionalData($formInstance)) {
            foreach ($this->dataProviders as $dataProvider) {
                /** @var OnlineDataProviderWrapperInterface $dataProvider */
                $results[$dataProvider->getType()] = $dataProvider->getResults($formInstance);
            }
            if (count($results)) {
                $results = $this->finishFilter($results);
            }
        } else {
            /** When Additional dates */
            $results = $this->additionalProvider->getResults($formInstance);
        }

        return $results;
    }

    private function isAdditionalData(OnlineSearchFormData $formData): bool
    {
        return $this->options['add_search_dates'] && $formData->isAddDates();
    }

    private function finishFilter(
        array $searchResults
    ) {
        $result = [];
        $isCommon = isset($searchResults['common']) && !empty($searchResults['common']);
        $isSpecials = isset($searchResults['special']) && !empty($searchResults['special']);
        if ($isCommon && $isSpecials) {
            $this->injectQueryIdInSpecial(reset($searchResults['common'])->getQueryId(), $searchResults['special']);
//            $result[] = array_shift($searchResults['special']);
            $result = array_merge($result, $searchResults['common'], $searchResults['special']);

            return $result;
        }
        foreach ($searchResults as $searchResult) {
            $result = array_merge($result, $searchResult);

        }

        return $result;
    }

    private function injectQueryIdInSpecial(string $queryId, array &$specials)
    {
        foreach ($specials as $special) {
            /** @var OnlineResultInstance $special */
            $special->setQueryId($queryId);
        }
    }
}
