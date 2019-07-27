<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\ResultBuilderInterface;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\SimpleResultBuilder;

class SearchResultCreator implements SearchResultCreatorInterface
{
    /**
     * @param SearchQuery $searchQuery
     * @param array $prices
     * @param int $roomAvailableAmount
     * @return ResultInterface
     */
    public function createResult(SearchQuery $searchQuery, array $prices, int $roomAvailableAmount): ResultInterface
    {
        $builder = $this->createCommonResult($searchQuery);
        $result = $builder
            ->addPrices($prices)
            ->setOkStatus()
            ->getResult();

        return $result;
    }

    /**
     * @param SearchQuery $searchQuery
     * @param SearchException $e
     * @return ResultInterface|Result
     */
    public function createErrorResult(SearchQuery $searchQuery, SearchException $e): ResultInterface
    {
        $builder = $this->createCommonResult($searchQuery);

        return $builder->setErrorStatus($e->getMessage(), $e->getType())->getResult();
    }

    private function createCommonResult(SearchQuery $searchQuery): ResultBuilderInterface
    {
        $resultBuilder = new SimpleResultBuilder();

        $resultBuilder
            ->createInstance()
            ->addBegin($searchQuery->getBegin())
            ->addEnd($searchQuery->getEnd())
            ->addAdults($searchQuery->getAdults())
            ->addChildren($searchQuery->getChildren() ?? 0)
            ->addChildrenAges($searchQuery->getChildrenAges())
            ->addTariff($searchQuery->getTariffId())
            ->addRoomType($searchQuery->getRoomTypeId());

        return $resultBuilder;
    }


}

