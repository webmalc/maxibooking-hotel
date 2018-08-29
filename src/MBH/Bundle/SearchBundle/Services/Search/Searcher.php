<?php


namespace MBH\Bundle\SearchBundle\Services\Search;

use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Searcher implements SearcherInterface
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
     * TODO: Надобно сделать сервис проверки лимитов и под каждый лимит отдельный класс как в restrictions например.
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): Result
    {
        try {
            $errors = $this->validator->validate($searchQuery);
            if (\count($errors)) {
                /** @var string $errors */
                throw new SearcherException('There is a problem in SearchQuery. '. $errors);
            }

            $this->searchLimitChecker->checkRoomCacheLimit($searchQuery);

            if (!$this->restrictionChecker->check($searchQuery)) {
                throw new SearcherException('Violation in restriction.');
            }

            $this->searchLimitChecker->checkDateLimit($searchQuery);
            $this->searchLimitChecker->checkTariffConditions($searchQuery);
            $this->searchLimitChecker->checkRoomTypePopulationLimit($searchQuery);

            $result = $this->resultComposer->composeResult($searchQuery);
            $this->searchLimitChecker->checkWindows($result, $searchQuery);
        } catch (SearchException $e) {
            $result = Result::createErrorResult($searchQuery, $e);
        }

        return $result;
    }

    /**
     * @return array|null
     */
    public function getRestrictionError(): ?array
    {
        return $this->restrictionChecker->getErrors();
    }

}