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
     * @param bool $isHideRestrictedResults
     * @return array
     * @throws AsyncResultReceiverException
     */
    public function receive(SearchConditions $conditions, bool $isHideRestrictedResults = true): array
    {
        $conditionsId = $conditions->getId();
        $searchHolder = $this->searchResultHolderRepository->findOneBy(['searchConditionsId' => $conditionsId]);
        if (!$searchHolder) {
            $searchHolder = new SearchResultHolder();
            $searchHolder->setSearchConditionsId($conditionsId);
        }

        $alreadyTakenResultsIds = $searchHolder->getTakenSearchResultIds();
        if (\count($alreadyTakenResultsIds) === $conditions->getExpectedResultsCount()) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if (\count($alreadyTakenResultsIds) > $conditions->getExpectedResultsCount()) {
            throw new AsyncResultReceiverException('Some error! Taken results more than Expected!');
        }

        $satisfyingResultsIds = $this->searchResultRepository->fetchOkResultIds($conditionsId);
        $restrictedResultsIds = $this->searchResultRepository->fetchErrorResultIds($conditionsId);

        $toTakeSatisfyingResultsIds = array_diff($satisfyingResultsIds, $alreadyTakenResultsIds);
        $toTakeRestrictedResultsIds = array_diff($restrictedResultsIds, $alreadyTakenResultsIds);
        $searchHolder->addTakenResultIds($toTakeSatisfyingResultsIds);
        $searchHolder->addTakenResultIds($toTakeRestrictedResultsIds);

        $dm = $this->searchResultRepository->getDocumentManager();
        $dm->persist($searchHolder);
        $dm->flush($searchHolder);

        $resultsIdsToReturn = $isHideRestrictedResults ? $toTakeSatisfyingResultsIds : array_merge($toTakeSatisfyingResultsIds, $toTakeRestrictedResultsIds);

        return  $this->searchResultRepository->fetchResultsByIds($resultsIdsToReturn);
    }


}