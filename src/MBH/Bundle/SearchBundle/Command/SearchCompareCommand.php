<?php


namespace MBH\Bundle\SearchBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\Search;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory as OldSearch;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Search\Search as NewSearch;
use Monolog\Handler\AbstractHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCompareCommand extends Command
{

    /** @var int */
    private const PRICE_MISMATCH_THRESHOLD = 10;

    /** @var NewSearch */
    private $newSearch;

    /** @var OldSearch */
    private $oldSearch;

    /** @var Search */
    private $oldSearchSimple;
    /**
     * @var \Monolog\Logger
     */
    private $logger;
    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(
        NewSearch $newSearch,
        OldSearch $oldSearch,
        LoggerInterface $logger,
        DocumentManager $dm,
        Search $oldSearchSimple
    ) {
        $this->newSearch = $newSearch;
        $this->oldSearch = $oldSearch->setWithTariffs();
        $this->logger = $logger;
        $this->dm = $dm;
        $this->oldSearchSimple = $oldSearchSimple;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mbh:search:compare');
        $this
            ->addOption('tariffId', null, InputOption::VALUE_OPTIONAL, 'TariffId for specific search.')
            ->addOption('roomTypeId', null, InputOption::VALUE_OPTIONAL, 'RoomTypeId for specific search.')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'Begin d.m.Y')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End d.m.Y')
            ->addOption('cache', null, InputOption::VALUE_NONE, 'is Use cache ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $begin = $input->getOption('begin') ? new \DateTime($input->getOption('begin')) : new \DateTime('midnight 01.05.2019');
        $end = $input->getOption('end') ? new \DateTime($input->getOption('end')) : new \DateTime('01.05.2019 +6 month');

        $isVerbose = $input->getOption('verbose');

        $handlers = $this->logger->getHandlers();
        /** @var AbstractHandler $handler */
        foreach ($handlers as $handler) {
            if ($isVerbose) {
                $handler->setLevel(100);
            }
        }

        $tariffIds = $input->getOption('tariffId') ? [$input->getOption('tariffId')] : [];
        $roomTypeId = $input->getOption('roomTypeId');
        if (null !== $roomTypeId) {
            $categoryId = [$this->dm->find(RoomType::class, $roomTypeId)->getCategory()->getId()];
        }


        $dates = $this->getDates($begin, $end);
        $combinations = $this->getCombinations();
        $hotelsIds = $this->dm->getRepository(Hotel::class)->getSearchActiveIds();
        $roomTypes = $this->dm->getRepository(RoomType::class)->findBy(['hotel.id' => ['$in' => $hotelsIds]]);
        $tariffs = $this->dm->getRepository(Tariff::class)->findBy(['hotel.id' => ['$in' => $hotelsIds]]);
        foreach ($dates as $date) {
            foreach ($combinations as $combination) {
                $data = [
                    'begin' => $date['begin']->format('d.m.Y'),
                    'end' => $date['end']->format('d.m.Y'),
                    'adults' => $combination['adults'],
                    'children' => $combination['children'],
                    'childrenAges' => $combination['childrenAges'] ?? [],
                    'roomTypes' => $categoryId ?? [],
                    'tariffs' => $tariffIds,
                    'errorLevel' => 0,
                    'isUseCache' => $input->getOption('cache')
                ];
                $newResult = $this->newSearchResults($data);
                $oldResult = $this->oldSearchResults($data);
                $newFilteredResults = $this->filterNewResults($newResult);
                $oldFilteredResults = $this->filterOldResults($oldResult);
                $compareData = $this->doResultsAsOneArray(
                    $newFilteredResults,
                    $oldFilteredResults,
                    $roomTypes,
                    $tariffs,
                    $data
                );
                $this->compareResults($compareData, $data);

            }

        }


    }

    private function compareResults(array $compareData, array $data)
    {
        $matched = array_filter(
            $compareData,
            function ($result) {
                $oldTotal = $result['old']['total'] ?? null;
                $newTotal = $result['new']['total'] ?? null;

                return $oldTotal !== null && $newTotal !== null && (abs(
                            $oldTotal - $newTotal
                        ) < self::PRICE_MISMATCH_THRESHOLD);
            }
        );

        $errors = array_filter(
            $compareData,
            function ($result) {
                $oldTotal = $result['old']['total'] ?? null;
                $newTotal = $result['new']['total'] ?? null;

                $newRoomsCount = $result['new']['roomsCount'] ?? null;
                $oldRoomsCount = $result['old']['roomsCount'] ?? null;

                $oneTotalIsNull = $oldTotal === null || $newTotal === null;
                $justOneOfResults = \is_array($result['old']) !== \is_array($result['new']);

                $differentRoomsCount = $newRoomsCount !== $oldRoomsCount;


                return ($oneTotalIsNull && ($newTotal !== $oldTotal))
                    || $justOneOfResults
                    || $differentRoomsCount
                    ;
            }
        );

        $mismatched = array_filter(
            $compareData,
            function ($result) {
                $oldTotal = $result['old']['total'] ?? null;
                $newTotal = $result['new']['total'] ?? null;

                return $oldTotal !== null && $newTotal !== null && abs(
                        $oldTotal - $newTotal
                    ) >= self::PRICE_MISMATCH_THRESHOLD;
            }
        );

        if (\count($compareData) !== array_sum(array_map('\count', [$matched, $mismatched, $errors]))) {
            throw new Exception('АШИПКА!');
        }

        $this->addLog($errors, 'ALERT');
        $this->addLog($mismatched, 'WARNING');
        $this->addLog($matched, 'DEBUG');
    }


    private function addLog(array $results, string $level = 'info')
    {
        foreach ($results as $result) {
            $message = implode(
                '-',
                [
                    $result['date'],
                    $result['roomType']['name'],
                    $result['roomType']['id'],
                    $result['tariff']['name'],
                    $result['tariff']['id'],
                    $result['adults'],
                    $result['children'],
                    implode('-', $result['childrenAges'] ?? []),
                    'old: '.$result['old']['total'] ?? 'fail',
                    'new: '.$result['new']['total'] ?? 'fail',
                    'oldRoomsCount: ' . $result['old']['roomsCount'],
                    'newRoomsCount: ' . $result['new']['roomsCount']
                ]

            );
            $this->logger->log($level, $message);
        }

    }

    private function doResultsAsOneArray(
        array $newResults,
        array $oldResults,
        array $roomTypes,
        array $tariffs,
        array $data
    ): array {
        $results = [];
        foreach ($roomTypes as $roomType) {
            $roomTypeId = $roomType->getId();
            foreach ($tariffs as $tariff) {
                $tariffId = $tariff->getId();
                $results[] = [
                    'date' => $data['begin'].'-'.$data['end'],
                    'roomType' => [
                        'id' => $roomTypeId,
                        'name' => $roomType->getName(),
                    ],
                    'tariff' => [
                        'id' => $tariffId,
                        'name' => $tariff->getName(),
                    ],
                    'old' => $oldResults[$roomTypeId][$tariffId] ?? null,
                    'new' => $newResults[$roomTypeId][$tariffId] ?? null,
                    'adults' => $data['adults'],
                    'children' => $data['children'],
                    'childrenAges' => $data['childrenAges'] ?? [],
                ];
            }
        }

        $results = array_filter(
            $results,
            function ($result) {
                return $result['old'] || $result['new'];
            }
        );

        return array_values($results);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     *
     */
    private function getDates(\DateTime $begin, \DateTime $end): iterable
    {
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day'));
        foreach ($period as $beginDay) {
            foreach ([4, 7, 14, 19] as $offset) {
                /**@var \DateTime $beginDay */
                if (\in_array((int)$beginDay->format('w'), [0, 3, 6], true)) {
                    yield [
                        'begin' => clone $beginDay,
                        'end' => (clone $beginDay)->modify("+ ${offset} days"),
                    ];
                }
            }
        }

    }

    private function getCombinations(): array
    {
        $combinations = [
            [
                'adults' => 1,
                'children' => 3,
                'childrenAges' => [2,6,15]
            ],
            [
                'adults' => 1,
                'children' => 1,
                'childrenAges' => [5]
            ],
            [
                'adults' => 2,
                'children' => 0
            ],
            [
                'adults' => 2,
                'children' => 2,
                'childrenAges' => [1,7]
            ],
            [
                'adults' => 1,
                'children' => 0
            ],
            [
                'adults' => 4,
                'children' => 1,
                'childrenAges' => [0]
            ]

        ];

        return $combinations;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    private function newSearchResults(array $data)
    {
        return $this->newSearch->searchSync($data);
    }

    private function oldSearchResults(array $data)
    {
        $query = new SearchQuery();
        if($data['tariffs']) {
            $tariffId = $data['tariffs'][0];
            $tariff = $this->dm->find(Tariff::class, $tariffId);
            $query->tariff = $tariff;
        }
        $query->begin = new \DateTime($data['begin']);
        $query->end = new \DateTime($data['end']);
        $query->adults = $data['adults'];
        $query->children = $data['children'];
        $query->childrenAges = $data['childrenAges'] ?? [];
        $query->limit = 40;
        $query->roomTypes = $data['roomTypes'] ?? [];
        $result = [];

        if ($query->tariff) {
            $result[] = $this->oldSearchSimple->search($query);
        } else {
            $searchResults = $this->oldSearch->search($query);
            if (\count($searchResults)) {
                foreach ($searchResults as $searchResult) {
                    $result[] = $searchResult['results'];
                }
            }
        }




        return array_merge(...$result);

    }

    private function filterNewResults(array $searchResults)
    {
        $result = [];
        foreach ($searchResults as $searchResult) {
            $result[$searchResult['resultRoomType']['id']][$searchResult['resultTariff']['id']] = [
                'begin' => (new \DateTime($searchResult['begin']))->format('d.m.Y'),
                'end' => (new \DateTime($searchResult['end']))->format('d.m.Y'),
                'roomTypeName' => $searchResult['resultRoomType']['name'],
                'tariffName' => $searchResult['resultTariff']['name'],
                'total' => reset($searchResult['prices'])['total'],
                'roomsCount' => $searchResult['minRoomsCount']
            ];
        }

        return $result;
    }

    private function filterOldResults(array $searchResults)
    {
        $result = [];
        foreach ($searchResults as $searchResult) {
            /** @var SearchResult $searchResult */
            $roomType = $searchResult->getRoomType();
            $tariff = $searchResult->getTariff();
            $result[$roomType->getId()][$tariff->getId()] = [
                'begin' => $searchResult->getBegin()->format('d.m.Y'),
                'end' => $searchResult->getEnd()->format('d.m.Y'),
                'roomTypeName' => $roomType->getName(),
                'tariffName' => $tariff->getName(),
                'total' => $searchResult->getPrice($searchResult->getAdults(), $searchResult->getChildren()),
                'roomsCount' => $searchResult->getRoomsCount()
            ];
        }

        return $result;
    }


}