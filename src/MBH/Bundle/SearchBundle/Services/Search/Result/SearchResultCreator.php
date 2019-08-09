<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result;


use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\ResultBuilderInterface;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\SimpleResultBuilder;

class SearchResultCreator implements SearchResultCreatorInterface
{

    /** @var RoomTypeManager */
    private $roomTypeManager;

    /** @var SharedDataFetcher */
    private $dataFetcher;

    /**
     * SearchResultCreator constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager, SharedDataFetcher $dataFetcher)
    {
        $this->roomTypeManager = $roomTypeManager;
        $this->dataFetcher = $dataFetcher;
    }


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
            ->addRoomAvailableAmount($roomAvailableAmount)
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
        if (true === $this->roomTypeManager->getIsUseCategories()) {
            $roomType = $this->dataFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());
            $category = $roomType->getCategory();
            if (null === $category) {
                throw new SearchResultComposerException('There is no category for roomType'.$roomType->getName());
            }
            $resultBuilder->addRoomTypeCategory($category->getId());
        }

        return $resultBuilder;
    }


}

