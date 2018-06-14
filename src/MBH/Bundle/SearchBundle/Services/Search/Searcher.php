<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Searcher
{
    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var  SearchLimitChecker*/
    private $searchLimitChecker;

    /** @var SearchResultComposer */
    private $resultComposer;

    /** @var ValidatorInterface  */
    private $validator;

    /** @var DataHolder */
    private $dataHolder;

    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        SearchResultComposer $resultComposer,
        ValidatorInterface $validator,
        DataHolder $dataHolder
)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->resultComposer = $resultComposer;
        $this->validator = $validator;
        $this->dataHolder = $dataHolder;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return SearchResult
     * @throws SearchException
     * @throws SearcherException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     */
    public function search(SearchQuery $searchQuery): SearchResult
    {

        $errors = $this->validator->validate($searchQuery);
        if (\count($errors)) {
            throw new SearcherException('There is a problem in SearchQuery. '. (string)$errors);
        }

        $currentTariffId = $searchQuery->getTariffId();

        $roomCaches = $this->dataHolder->getNecessaryRoomCaches($searchQuery);
        $roomCaches = $this->searchLimitChecker->checkRoomCacheLimit($roomCaches, $currentTariffId, $searchQuery->getDuration());

        $this->preFilter($searchQuery);
        $this->checkRestrictions($searchQuery);
        $this->searchLimitChecker->checkDateLimit($currentTariffId);
        $this->searchLimitChecker->checkTariffConditions($searchQuery);
        $this->searchLimitChecker->checkRoomTypePopulationLimit($searchQuery);

        $searchResult = $this->resultComposer->composeResult($searchQuery, $roomCaches);
        $this->searchLimitChecker->checkWindows($searchResult);

        return $searchResult;
    }

    /**
     *
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function preFilter(SearchQuery $searchQuery): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $mustFields = [
            'begin',
            'end',
            'tariffId',
            'roomTypeId'
        ];
        foreach ($mustFields as $mustField) {
            if (!$accessor->getValue($searchQuery, $mustField)) {
                throw new SearchException('Terminate Search cause error in search query');
            }
        }
    }

    /**
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function checkRestrictions(SearchQuery $searchQuery): void
    {
        $checked = $this->restrictionChecker->check($searchQuery);
        if (!$checked) {
            throw new SearchException('Violation in restriction.');
        }
    }

    public function getRestrictionError(): ?array
    {
        return $this->restrictionChecker->getErrors();
    }

}