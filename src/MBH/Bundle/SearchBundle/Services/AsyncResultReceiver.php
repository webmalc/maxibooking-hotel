<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Document\SearchResultRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;

class AsyncResultReceiver
{

    /** @var SearchResultRepository */
    private $searchResultRepository;

    /** @var SearchResultHolderRepository */
    private $searchResultHolderRepository;

    /**
     * AsyncResultReceiver constructor.
     * @param SearchResultRepository $searchResultRepository
     * @param SearchResultHolderRepository $searchResultHolderRepository
     */
    public function __construct(SearchResultRepository $searchResultRepository, SearchResultHolderRepository $searchResultHolderRepository)
    {
        $this->searchResultRepository = $searchResultRepository;
        $this->searchResultHolderRepository = $searchResultHolderRepository;
    }


    /**
     * @param SearchConditions $conditions
     * @return array
     * @throws AsyncResultReceiverException
     */
    public function receive(SearchConditions $conditions): array
    {
        $conditionsId = $conditions->getId();
        $searchHolder = $this->searchResultHolderRepository->findOneBy(['searchConditionsId' => $conditionsId]);
        if (!$searchHolder) {
            $searchHolder = new SearchResultHolder();
            $searchHolder->setSearchConditionsId($conditionsId);
        }

        $takenSearchResultIds = $searchHolder->getTakenSearchResultIds();
        if (\count($takenSearchResultIds) === $conditions->getExpectedResultsCount()) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if (\count($takenSearchResultIds) > $conditions->getExpectedResultsCount()) {
            throw new AsyncResultReceiverException('Some error! Taken results more than Expected!');
        }

        $resultsOkIds = $this->searchResultRepository->fetchOkResultIds($conditionsId);
        $resultsErrorIds = $this->searchResultRepository->fetchErrorResultIds($conditionsId);

        $toTakeResultsOkIds = array_diff($resultsOkIds, $takenSearchResultIds);
        $toTakeResultsErrorIds = array_diff($resultsErrorIds, $takenSearchResultIds);
        $searchHolder->addTakenResultIds($toTakeResultsOkIds);
        $searchHolder->addTakenResultIds($toTakeResultsErrorIds);

        $dm = $this->searchResultRepository->getDocumentManager();
        $dm->persist($searchHolder);
        $dm->flush($searchHolder);

        return  $this->searchResultRepository->fetchResultsByIds($toTakeResultsOkIds);
    }


}