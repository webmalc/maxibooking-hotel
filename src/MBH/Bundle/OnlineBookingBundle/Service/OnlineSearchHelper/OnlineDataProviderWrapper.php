<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters\OnlineSorterInterface;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

class OnlineDataProviderWrapper implements OnlineDataProviderWrapperInterface
{

    /** @var OnlineDataProviderInterface */
    private $dataProvider;

    /** @var OnlineSorterInterface */
    private $onlineSorter;

    /**
     * OnlineDataProviderContainer constructor.
     * @param OnlineDataProviderInterface $dataProvider
     * @param OnlineSorterInterface $onlineSorter
     */
    public function __construct(OnlineDataProviderInterface $dataProvider, OnlineSorterInterface $onlineSorter)
    {
        $this->dataProvider = $dataProvider;
        $this->onlineSorter = $onlineSorter;
    }

    public function getResults(OnlineSearchFormData $formData): array
    {
        $clonedFormData = clone $formData;
        $result = $this->dataProvider->search($clonedFormData);
        $result = $this->onlineSorter->sort($result);

        return $result;
    }

    public function getType(): string
    {
        return $this->dataProvider->getType();
    }


}