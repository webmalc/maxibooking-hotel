<?php


namespace MBH\Bundle\SearchBundle\Services\Search;

use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
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

    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        SearchResultComposer $resultComposer,
        ValidatorInterface $validator
)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->resultComposer = $resultComposer;
        $this->validator = $validator;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws SearcherException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchLimitCheckerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): Result
    {

        //** TODO: Надобно сделать сервис проверки лимитов и под каждый лимит отдельный класс
        // как в restrictions например.
        // */
        $errors = $this->validator->validate($searchQuery);
        if (\count($errors)) {
            throw new SearcherException('There is a problem in SearchQuery. '. (string)$errors);
        }

        $this->searchLimitChecker->checkRoomCacheLimit($searchQuery);

        if (!$this->restrictionChecker->check($searchQuery)) {
            throw new SearcherException('Violation in restriction.');
        }

        $this->searchLimitChecker->checkDateLimit($searchQuery);
        $this->searchLimitChecker->checkTariffConditions($searchQuery);
        $this->searchLimitChecker->checkRoomTypePopulationLimit($searchQuery);

        $searchResult = $this->resultComposer->composeResult($searchQuery);
        $this->searchLimitChecker->checkWindows($searchResult);

        return $searchResult;
    }

    /**
     * @return array|null
     */
    public function getRestrictionError(): ?array
    {
        return $this->restrictionChecker->getErrors();
    }

}