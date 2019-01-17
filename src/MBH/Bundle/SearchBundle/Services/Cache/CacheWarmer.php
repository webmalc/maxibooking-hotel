<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\GuestCombinator;
use MBH\Bundle\SearchBundle\Services\Search\CacheSearcher;
use MBH\Bundle\SearchBundle\Services\Search\WarmUpSearcher;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use Psr\Log\LoggerInterface;

class CacheWarmer
{

    /** @var int */
    private const QUEUE_CHUNK_NUM = 75;

    public const MIN_BOOKING_LENGTH = 5;

    public const MAX_BOOKING_LENGTH = 16;

    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /** @var LoggerInterface */
    private $logger;
    /**
     * @var SearchConditionsCreator
     */
    private $conditionCreator;
    /**
     * @var CacheSearcher
     */
    private $warmUpSearcher;
    /**
     * @var GuestCombinator
     */
    private $combinator;
    /**
     * @var TariffRepository
     */
    private $tariffRepository;

    /**
     * CacheWarmer constructor.
     * @param SearchConditionsCreator $creator
     * @param SearchQueryGenerator $queryGenerator
     * @param WarmUpSearcher $searcher
     * @param GuestCombinator $combinator
     * @param TariffRepository $tariffRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchConditionsCreator $creator,
        SearchQueryGenerator $queryGenerator,
        WarmUpSearcher $searcher,
        GuestCombinator $combinator,
        TariffRepository $tariffRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->conditionCreator = $creator;
        $this->queryGenerator = $queryGenerator;
        $this->warmUpSearcher = $searcher;
        $this->combinator = $combinator;
        $this->tariffRepository = $tariffRepository;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function warmUp(\DateTime $begin, \DateTime $end): void
    {

        $this->logger->info('Start cache warmUp');
        $datesArray = $this->getDates($begin, $end);
        $dm = $this->tariffRepository->getDocumentManager();
        $hotelIds = $dm->getRepository(Hotel::class)->getSearchActiveIds();
        $tariffs = $this->tariffRepository->getTariffsByHotelsIds($hotelIds);
        $combinationTypes = $this->getCombinations($tariffs);
        $totalDates = \count($datesArray);
        foreach ($datesArray as $dates) {
            $this->logger->info('WarmUp for '.$dates['begin']->format('d.m.Y').'-'.$dates['end']->format('d.m.Y'));
            $this->logger->info('Left '.$totalDates.' dates');
            $totalDates--;
            $this->warmUpDateCombination($dates['begin'], $dates['end'], $combinationTypes);
        }

    }

    /**
     * @param array $tariffs
     * @return array
     */
    protected function getCombinations(array $tariffs = []): array
    {
        return $this->combinator->getCombinations($tariffs);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array|null $roomTypeIds
     * @param array|null $tariffIds
     * @param array|null $combination
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function warmUpSpecificQuery(
        \DateTime $begin,
        \DateTime $end,
        ?array $roomTypeIds = [],
        ?array $tariffIds = [],
        ?array $combination = []
    ): void {
        $conditionsData = [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'additionalBegin' => 0,
            'additionalEnd' => 0,
            'isUseCache' => true,
            // isThisWarmUp - Dirty hack for disable ChildrenAges validator when warmUp process /
            'isThisWarmUp' => true,
            'adults' => $combination['adults'],
            'children' => $combination['children'],
            'tariffs' => $tariffIds,
            'roomTypes' => $roomTypeIds,
        ];

        $this->doWarmUp($conditionsData);
    }

    private function getDates(\DateTime $begin, \DateTime $end): array
    {
        $dates = [];
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day'));
        foreach ($period as $beginDay) {
            foreach (range(self::MIN_BOOKING_LENGTH, self::MAX_BOOKING_LENGTH) as $offset) {
                /**@var \DateTime $beginDay */
                $dates[] = [
                    'begin' => clone $beginDay,
                    'end' => (clone $beginDay)->modify("+ ${offset} days"),
                ];
            }
        }

        return $dates;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $combinationTypes
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    private function warmUpDateCombination(\DateTime $begin, \DateTime $end, array $combinationTypes): void
    {
        $sharedConditions = [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'additionalBegin' => 0,
            'additionalEnd' => 0,
            'isUseCache' => true,
            // isThisWarmUp - Dirty hack for disable ChildrenAges validator when warmUp process /
            'isThisWarmUp' => true,
        ];

        foreach ($combinationTypes as $combinationType) {
            /** @var CombinationInterface $combinationType */
            $guestCombinations = $combinationType->getCombinations();
            $this->logger->info('Combination.', $guestCombinations);
            foreach ($guestCombinations as $combination) {
                $conditionsData = array_merge(
                    $sharedConditions,
                    $combination,
                    ['tariffs' => $combinationType->getTariffIds()]
                );
                $this->doWarmUp($conditionsData, $combinationType->getPriority());
            }
        }
    }

    /**
     * @param array $conditionsData
     * @param int $priority
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    protected function doWarmUp(array $conditionsData, int $priority = 1): void
    {
        $conditions = $this->conditionCreator->createSearchConditions($conditionsData);
        $conditions->setId('warmerConditions');
        $queries = $this->queryGenerator->generate($conditions);
        array_map(
            function (SearchQuery $query) {
                $query->unsetConditions();
            },
            $queries
        );
        $queryChunks = array_chunk($queries, self::QUEUE_CHUNK_NUM);
        foreach ($queryChunks as $chunk) {
            $this->warmUpSearcher->search($chunk, '', ['priority' => $priority]);
        }
    }
}