<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class OnlineSearchHelper
{

    /** @var array  */
    private $options;

    /** @var  \SplObjectStorage */
    private $resultsGenerators;

    /**
     * OnlineSearchHelper constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->resultsGenerators = new \SplObjectStorage();
    }

    public function addGenerator(AbstractResultGenerator $generator){
        $this->resultsGenerators->attach($generator);
    }

    public function getResults(OnlineSearchFormData $formInstance)
    {
        $results = new ArrayCollection();
        foreach ($this->resultsGenerators as $generator) {
            /** @var AbstractResultGenerator $generator */
            if ( 'common' === $generator->getType() && $this->options['add_search_dates']) {
                $generator->setSearchConfigurator(
                    function (SearchQuery $searchQuery, SearchFactory $search) {
                        $range = $this->options['add_search_dates'];
                        $searchQuery->range = $range;
                        $search->setAdditionalDates($range);
                    }
                );
            }
            /** @var AbstractResultGenerator $generator */
            $results->add(array_merge($generator->getResults($formInstance)->toArray()));
        }

        return $results;
    }
}