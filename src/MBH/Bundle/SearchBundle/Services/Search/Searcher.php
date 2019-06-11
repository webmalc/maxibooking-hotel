<?php


namespace MBH\Bundle\SearchBundle\Services\Search;

use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionLimitException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;
use Psr\SimpleCache\InvalidArgumentException;
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
     * @throws SearcherException
     * @throws MongoDBException
     * @throws DataFetchQueryException
     * @throws SearchResultComposerException
     * @throws SharedFetcherException
     * @throws InvalidArgumentException
     */
    public function search(SearchQuery $searchQuery): Result
    {
        try {
            $errors = $this->validator->validate($searchQuery);
            $conditions = $searchQuery->getSearchConditions();
            if (!$conditions) {
                throw new SearcherException('There is a problem in SearchQuery. No conditions. ');
            }
            if ($errors->count() && !$conditions->isThisWarmUp()) {
                $errors = $this->validator->validate($searchQuery, new ChildrenAgesSameAsChildren());
            }
            if ($errors->count()) {
                /** @var string $errors */
                throw new SearcherException('There is a problem in SearchQuery. '. $errors);
            }

            $this->searchLimitChecker->checkRoomCacheLimit($searchQuery);

            if (!$this->restrictionChecker->check($searchQuery)) {
                $errors = $this->restrictionChecker->getErrors();
                $message = implode('_',$errors[0]);
                throw new RestrictionLimitException('Violation in restriction.'.$message);
            }

            $this->searchLimitChecker->checkDateLimit($searchQuery);
            $this->searchLimitChecker->checkTariffConditions($searchQuery);
            /** TODO: some conditions may be various (child free etc...) */
            $this->searchLimitChecker->checkRoomTypePopulationLimit($searchQuery);

            $result = $this->resultComposer->composeResult($searchQuery);
            $virtualRoom = $this->searchLimitChecker->checkWindows($result, $searchQuery);
            $this->resultComposer->insertVirtualRoom($virtualRoom, $result);
        } catch (SearchException $e) {
            $result = Result::createErrorResult($searchQuery, $e);
        }

        return $result;
    }

}