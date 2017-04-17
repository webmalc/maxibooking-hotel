<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;

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

//            if ('special' === $generator->getType()) {
//                if ($formInstance->getSpecial() && $formInstance->getRoomType() && 'special' === $generator->getType()) {
//                    $generator->setSearchConfigurator(
//                        function (SearchQuery $searchQuery) use ($formInstance) {
//                            $searchQuery->setSpecial($formInstance->getSpecial());
//                            $searchQuery->roomTypes = $this->helper->toIds([$formInstance->getRoomType()]);
//                            $searchQuery->forceRoomTypes = true;
//                            $searchQuery->setPreferredVirtualRoom($formInstance->getSpecial()->getVirtualRoom());
//                        }
//                    );
//
//                }
//            }
//            /** @var AbstractResultGenerator $generator */
//            if ( 'common' === $generator->getType() && $this->options['add_search_dates']) {
//                $generator->setSearchConfigurator(
//                    function (SearchQuery $searchQuery, SearchFactory $search) {
//                        $range = $this->options['add_search_dates'];
//                        $searchQuery->range = $range;
//                        $search->setAdditionalDates($range);
//                    }
//                );
//            }
//            /** @var AbstractResultGenerator $generator */
//            $results[$generator->getType()] = $generator->getResults($formInstance)->toArray();
//        }

            $results[$generator->getType()] = $generator->getResults($formInstance)->toArray();
        }

        return $this->finishFilter($results);
    }

//    public function searchSpecialOnly(
//        OnlineSearchFormData $formInstance
//    ) {
//        foreach ($this->resultsGenerators as $generator) {
//            if ('special' === $generator->getType()) {
//                if ($formInstance->getSpecial() && $formInstance->getRoomType() && 'special' === $generator->getType()
//                ) {
//                    $generator->setSearchConfigurator(
//                        function (SearchQuery $searchQuery) use ($formInstance) {
//                            $searchQuery->setSpecial($formInstance->getSpecial());
//                            $searchQuery->roomTypes = $this->helper->toIds([$formInstance->getRoomType()]);
//                            $searchQuery->forceRoomTypes = true;
//                            $searchQuery->setPreferredVirtualRoom($formInstance->getSpecial()->getVirtualRoom());
//                        }
//                    );
//
//                }
//            }
//        }
//    }

    private function finishFilter(
        array $searchResults
    ) {
        $result = [];
        foreach ($searchResults as $key => $sResults) {
            //Тут можно фильтровать
            $result = array_merge($result, $sResults);
        }

        return $result;
    }
}