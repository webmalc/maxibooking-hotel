<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

class OnlineSearchHelper
{

    /** @var array */
    private $options;

    /** @var  \SplObjectStorage */
    private $resultsGenerators;
    /** @var  Helper */
    private $helper;

    /**
     * OnlineSearchHelper constructor.
     * @param array $options
     * @param Helper $helper
     */
    public function __construct(array $options, Helper $helper)
    {
        $this->options = $options;
        $this->resultsGenerators = new \SplObjectStorage();
        $this->helper = $helper;
    }

    public function addGenerator(AbstractResultGenerator $generator)
    {
        $this->resultsGenerators->attach($generator);
    }

    public function getResults(OnlineSearchFormData $formInstance)
    {
        $results = [];
        foreach ($this->resultsGenerators as $generator) {
            $results[$generator->getType()] = $generator->getResults($formInstance)->toArray();
        }
        if (count($results)) {
            $results = $this->finishFilter($results);
        }

        return $results;
    }

    private function finishFilter(
        array $searchResults
    ) {
        $result = [];
        $isCommon = isset($searchResults['common']) && !empty($searchResults['common']);
        $isSpecials = isset($searchResults['special']) && !empty($searchResults['special']);
        if ($isCommon && $isSpecials) {
            $this->injectQueryIdInSpecial(reset($searchResults['common'])->getQueryId(), $searchResults['special']);
#           $result[] = array_shift($searchResults['special']);
            $result = array_merge($result , $searchResults['common'] , $searchResults['special']);

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
