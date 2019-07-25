<?php


namespace MBH\Bundle\SearchBundle\Services\Search;

use function count;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionLimitException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\Search\Result\SearchResultCreatorInterface;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Searcher implements SearcherInterface
{
    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var  SearchLimitChecker*/
    private $searchLimitChecker;

    /** @var ValidatorInterface  */
    private $validator;
    /**
     * @var PriceSearcher
     */
    private $priceSearcher;

    /** @var WindowsChecker */
    private $windowsChecker;
    /**
     * @var SearchResultCreatorInterface
     */
    private $resultCreator;

    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearchLimitChecker $limitChecker,
        SearchResultCreatorInterface $resultCreator,
        ValidatorInterface $validator,
        PriceSearcher $priceSearcher,
        WindowsChecker $windowsChecker
)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searchLimitChecker = $limitChecker;
        $this->resultCreator = $resultCreator;
        $this->validator = $validator;
        $this->priceSearcher = $priceSearcher;
        $this->windowsChecker = $windowsChecker;
    }


    /**
     * TODO: Надобно сделать сервис проверки лимитов и под каждый лимит отдельный класс как в restrictions например.
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws DataFetchQueryException
     * @throws MongoDBException
     * @throws SearcherException
     * @throws SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): ResultInterface
    {
        try {
            $errors = $this->validator->validate($searchQuery);
            $conditions = $searchQuery->getSearchConditions();
            if (!$conditions) {
                throw new SearcherException('There is a problem in SearchQuery. No conditions. ');
            }
            if (!count($errors) && !$conditions->isThisWarmUp()) {
                $errors = $this->validator->validate($searchQuery, new ChildrenAgesSameAsChildren());
            }
            if (count($errors)) {
                /** @var string $errors */
                throw new SearcherException('There is a problem in SearchQuery. '. $errors);
            }

            $roomCaches = $this->searchLimitChecker->checkRoomCacheLimit($searchQuery);
            $roomAvailableAmount = min(array_column($roomCaches, 'leftRooms'));

            if (!$this->restrictionChecker->check($searchQuery)) {
                $errors = $this->restrictionChecker->getErrors();
                $message = implode('_',$errors[0]);
                throw new RestrictionLimitException('Violation in restriction.'.$message);
            }

            $this->searchLimitChecker->checkDateLimit($searchQuery);
            $this->searchLimitChecker->checkTariffConditions($searchQuery);
            /** TODO: some conditions may be various (child free etc...) */
            $this->searchLimitChecker->checkRoomTypePopulationLimit($searchQuery);

            $prices = $this->priceSearcher->searchPrice($searchQuery);

            $result = $this->resultCreator->createResult($searchQuery, $prices, $roomAvailableAmount);

            $this->windowsChecker->checkWindows($result, $searchQuery);

        } catch (SearchException $e) {
            $result = $this->resultCreator->createErrorResult($searchQuery, $e);
        }

        return $result;
    }

}