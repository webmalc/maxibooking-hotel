<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineSearchAdapter;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Events\SearchEvent;
use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\QueryGroupException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Services\QueryGroups\AsyncQueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupCreator;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchCombinationsGenerator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search
{

    /** @var bool */
    public const PRE_RESTRICTION_CHECK = false;

    /** @var SearcherFactory */
    private $searcherFactory;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var DocumentManager */
    private $dm;

    /** @var SearchConditionsCreator */
    private $conditionsCreator;

    /** @var SearchCombinationsGenerator */
    private $combinationsGenerator;

    /** @var ProducerInterface */
    private $producer;

    /** @var FinalSearchResultsAnswerManager */
    private $resultsAnswerManager;

    /** @var QueryGroupCreator */
    private $queryGroupCreator;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param SearcherFactory $factory
     * @param DocumentManager $documentManager
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchCombinationsGenerator $queryGenerator
     * @param ProducerInterface $producer
     * @param FinalSearchResultsAnswerManager $builder
     * @param QueryGroupCreator $groupCreator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearcherFactory $factory,
        DocumentManager $documentManager,
        SearchConditionsCreator $conditionsCreator,
        SearchCombinationsGenerator $queryGenerator,
        ProducerInterface $producer,
        FinalSearchResultsAnswerManager $builder,
        QueryGroupCreator $groupCreator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcherFactory = $factory;
        $this->dm = $documentManager;
        $this->conditionsCreator = $conditionsCreator;
        $this->combinationsGenerator = $queryGenerator;
        $this->producer = $producer;
        $this->resultsAnswerManager = $builder;
        $this->queryGroupCreator = $groupCreator;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @param array $data
     * @param null $grouping
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @param bool $separatedError
     * @return mixed
     * @throws GroupingFactoryException
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function searchSync(
        array $data,
        $grouping = null,
        bool $isCreateJson = false,
        bool $isCreateAnswer = false,
        bool $separatedError = true
    ) {
        $event = new SearchEvent();
        $conditions = $this->createSearchConditions($data);

        $event->setSearchConditions($conditions);
        $this->dispatcher->dispatch(SearchEvent::SEARCH_SYNC_START, $event);

        $searchCombinations = $this->combinationsGenerator->generate($conditions);
        $searchQueries = $searchCombinations->createSearchQueries($conditions);

        $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());

        $results = [];
        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }
        foreach ($searchQueries as $searchQuery) {
            $results[] = $searcher->search($searchQuery);
        }

        /**
         * Вынести отсюда формирование результатов. Не забыть что костыль в адаптере для онлайн прикручен с фильтрацией
         * @see OnlineSearchAdapter::search()
         *
         */
        $results = $this->resultsAnswerManager->createAnswer(
            $results,
            $conditions->getErrorLevel(),
            $isCreateJson,
            $isCreateAnswer,
            $grouping,
            $conditions->getSearchHash(),
            $separatedError
        );

        $this->dispatcher->dispatch(SearchEvent::SEARCH_SYNC_END, $event);

        return $results;
    }


    /**
     * @param array $data
     * @return string
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws QueryGroupException
     */
    public function searchAsync(array $data): string
    {

        $conditions = $this->createSearchConditions($data);
        $searchCombinations = $this->combinationsGenerator->generate($conditions);
        $queryGroups = $this->queryGroupCreator->createQueryGroups(
            $conditions,
            $searchCombinations,
            'QueryGroupByRoomType'
        );

        $conditionsId = $conditions->getId();
        $countQueries = array_reduce(
            $queryGroups,
            static function ($carry, $queryGroup) {
                /** @var AsyncQueryGroupInterface $queryGroup */
                return $carry + $queryGroup->countQueries();
            }
        );

        $conditions->setExpectedResultsCount($countQueries);
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);

        /** @var AsyncQueryGroupInterface|QueryGroupInterface $queryGroup */
        foreach ($queryGroups as $queryGroup) {
            $queryGroup->unsetConditions();
            $message = [
                'conditionsId' => $conditionsId,
                'searchQueriesGroup' => serialize($queryGroup),
            ];
            $msgBody = json_encode($message);
            $this->producer->publish(
                $msgBody,
                '',
                [
                    'priority' => $queryGroup->getQueuePriority(),

                ]
            );
        }

        return $conditions->getId();
    }


    /**
     * @param array $data
     * @return SearchConditions
     * @throws SearchConditionException
     */
    private function createSearchConditions(array $data): SearchConditions
    {

        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $this->saveQueryStat($conditions);

        return $conditions;
    }

    /**
     * @param SearchConditions $conditions
     */
    private function saveQueryStat(SearchConditions $conditions): void
    {
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);
    }

}