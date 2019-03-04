<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;



use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders\DataProviderInterface;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters\OnlineSorterInterface;

class OnlineDataProviderWrapper implements OnlineDataProviderWrapperInterface
{
    private $dataProvider;

    /** @var OnlineSorterInterface */
    private $onlineSorter;

    /**
     * OnlineDataProviderContainer constructor.
     * @param DataProviderInterface $dataProvider
     * @param OnlineSorterInterface $onlineSorter
     */
    public function __construct(DataProviderInterface $dataProvider, OnlineSorterInterface $onlineSorter)
    {
        $this->dataProvider = $dataProvider;
        $this->onlineSorter = $onlineSorter;
    }

    public function getResults(OnlineSearchFormData $formData): array
    {
        $clonedFormData = clone $formData;

        //** TODO: Костыль для спецпредложений! */
        if ($formData->getSpecial() && $this->getType() !== 'special') {
            return [];
        }
        $result = $this->dataProvider->search($clonedFormData);
        $result = $this->onlineSorter->sort($result, $clonedFormData);

        return $result;
    }

    public function getType(): string
    {
        return $this->dataProvider->getType();
    }


}