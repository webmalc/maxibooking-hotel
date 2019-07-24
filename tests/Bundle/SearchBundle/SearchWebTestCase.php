<?php


namespace Tests\Bundle\SearchBundle;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;

abstract class SearchWebTestCase extends WebTestCase
{
    /** @var DocumentManager */
    protected $dm;

    public function setUp()
    {
        $this->client = $this->makeAuthenticatedClient();
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $this->getContainer()->get('snc_redis.results_client')->flushall();
    }

    protected function createConditionData(iterable $data): array
    {
        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");
        $hotelNames = $data['hotels'];
        $allHotels = $this->dm->getRepository(Hotel::class)->findAll();
        $hotels = [];
        $tariffs = [];
        $roomTypes = [];
        $tariffNames = $data['tariffs'];
        $roomTypeNames = $data['roomTypes'];
        foreach ($hotelNames as $hotelName) {
            $hotel = $this->getDocumentFromArrayByFullTitle($allHotels, $hotelName);
            $hotels[] = $hotel;
            /** @var Hotel $hotel */
            $allRoomTypes = $hotel->getRoomTypes()->toArray();
            $allTariffs = $hotel->getTariffs()->toArray();

            if (\count($tariffNames)) {
                foreach ($tariffNames as $tariffName) {
                    $tariffs[] = $this->getDocumentFromArrayByFullTitle($allTariffs, $tariffName);
                }
            }

            if (\count($roomTypeNames)) {
                foreach ($roomTypeNames as $roomTypeName) {
                    $roomTypes[] = $this->getDocumentFromArrayByFullTitle($allRoomTypes, $roomTypeName);
                }
            }

        }

        if (!\count($hotels)) {
            foreach ($allHotels as $hotelInstance) {
                $hotel = $hotelInstance;
                /** @var Hotel $hotel */
                $allRoomTypes = $hotel->getRoomTypes()->toArray();
                $allTariffs = $hotel->getTariffs()->toArray();
                if (\count($tariffNames)) {
                    foreach ($tariffNames as $tariffName) {
                        $tariffs[] = $this->getDocumentFromArrayByFullTitle($allTariffs, $tariffName);
                    }
                }

                if (\count($roomTypeNames)) {
                    foreach ($roomTypeNames as $roomTypeName) {
                        $roomTypes[] = $this->getDocumentFromArrayByFullTitle($allRoomTypes, $roomTypeName);
                    }
                }

            }
        }


        return [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'hotels' => Helper::toIds($hotels),
            'roomTypes' => Helper::toIds($roomTypes),
            'tariffs' => Helper::toIds($tariffs),
            'adults' => $data['adults'],
            'children' => $data['children'],
            'childrenAges' => $data['childrenAges'],
            'additionalBegin' => $data['additionalBegin'] ?? 0,
            'additionalEnd' => $data['additionalEnd'] ?? null
        ];

    }


    /**
     * @param iterable $documents
     * @param string $documentFullTitle
     * @return mixed
     */
    protected function getDocumentFromArrayByFullTitle(iterable $documents, string $documentFullTitle)
    {
        $filter = static function ($document) use ($documentFullTitle) {
            return $document->getFullTitle() === $documentFullTitle;
        };
        $documentFiltered = array_filter($documents, $filter);

        return reset($documentFiltered);
    }

    /**
     * @param array $data
     * @return SearchQuery
     */
    protected function createSearchQuery(array $data): SearchQuery
    {
        $queries = $this->createSearchQueries($data);

        return reset($queries);

    }

    protected function createSearchQueries(array $data): array
    {
        $conditions = $this->createSearchCondition($data);
        $searchQueries = $this->getContainer()->get('mbh_search.search_combinations_generator')->generate($conditions)->createSearchQueries($conditions);

        return $searchQueries;
    }

    protected function createSearchCondition($data): SearchConditions
    {
        /** @var Hotel $hotel */
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy(['fullTitle' => $data['hotelFullTitle']]);

        $roomTypes = $hotel->getRoomTypes()->toArray();
        $hotelTariffs = $hotel->getTariffs()->toArray();

        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");
        if ($data['tariffFullTitle']) {
            $searchTariff = new ArrayCollection([$this->getDocumentFromArrayByFullTitle($hotelTariffs, $data['tariffFullTitle'])]);
        } else {
            $searchTariff = new ArrayCollection();
        }

        if ($data['roomTypeFullTitle']) {
            $searchRoomType = new ArrayCollection([$this->getDocumentFromArrayByFullTitle($roomTypes, $data['roomTypeFullTitle'])]);
        } else {
            $searchRoomType = new ArrayCollection();
        }

        $searchHash = uniqid(gethostname(), true);

        $conditions = new SearchConditions();
        $conditions
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdditionalBegin($data['additionalBegin'] ?? 0)
            ->setAdditionalEnd($data['additionalEnd'] ?? 0)
            ->setSearchHash($searchHash)
            ->setTariffs($searchTariff)
            ->setRoomTypes($searchRoomType)
            ->setId('fakeId')
        ;

        if ($data['adults'] ?? null) {
            $conditions->setAdults($data['adults']);
        }
        if($data['children'] ?? null) {
            $conditions->setChildren($data['children']);
            $conditions->setChildrenAges($data['childrenAges']);
        }

        return $conditions;
    }

}

// Data for Example Do not remove.
//yield [
//    [
//        'beginOffset' => 3,
//        'endOffset' => 8,
//        'tariffFullTitle' => 'Основной тариф',
//        'roomTypeFullTitle' => 'Стандартный двухместный',
//        'hotelFullTitle' => 'Отель Волга',
//        'adults' => 1,
//        'expected' => [
//            'prices' => ['1_0' => 11280],
//            'minCache' => 5
//        ],
//    ]
//];