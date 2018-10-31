<?php


namespace MBH\Bundle\SearchBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchCompareController
 * @package MBH\Bundle\SearchBundle\Controller
 * @Route("/compare")
 */
class SearchCompareController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @Route("/")
     */
    public function searchAction(Request $request): Response
    {
//        $data = json_decode($request->getContent(), true);
        $data = [
            'begin' => $request->request->get('begin'),
            'end' => $request->get('end'),
            'adults' => (int) $request->get('adults'),
            'children' => (int) $request->get('children'),
            'childrenAges' => $request->get('childrenAges'),
        ];



        $newSearchResults = $this->newSearchResults($data ?? []);
        $oldSearchResults = $this->oldSearchResults($data);
        $new = $this->filterNewResults($newSearchResults);
        $old = $this->filterOldResults($oldSearchResults);

        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $hotelsIds = $dm->getRepository(Hotel::class)->getSearchActiveIds();
        $roomTypes = $dm->getRepository(RoomType::class)->findBy(['hotel.id' => ['$in' => $hotelsIds]]);
        $tariffs = $dm->getRepository(Tariff::class)->findBy(['hotel.id' => ['$in' => $hotelsIds]]);

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
                    'old' => $old[$roomTypeId][$tariffId] ?? null,
                    'new' => $new[$roomTypeId][$tariffId] ?? null,
                ];
            }
        }

        $results = array_filter(
            $results,
            function ($result) {
                return $result['old'] || $result['new'];
            }
        );

        $result[$data['begin']
        .'_'
        .$data['end']
        .'_'
        .$data['adults']
        .'_'
        .$data['children'].
        '_'.
        implode(
            '_',
            $data['childrenAges'] ?? []
        )] = array_values($results);

        $response = new JsonResponse(json_encode($result, JSON_UNESCAPED_UNICODE), 200, [], true);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;

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
        return $this->get('mbh_search.search')->searchSync($data, true, null, false, false);
    }

    private function oldSearchResults(array $data)
    {
        $query = new SearchQuery();
        $query->begin = new \DateTime($data['begin']);
        $query->end = new \DateTime($data['end']);
        $query->adults = $data['adults'];
        $query->children = $data['children'];
        $query->childrenAges = $data['childrenAges'] ?? [];

        $search = $this->get('mbh.package.search')->setWithTariffs();

        $searchResults = $search->search($query);
        $result = [];
        if (\count($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $result[] = $searchResult['results'];
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
            ];
        }

        return $result;
    }

}