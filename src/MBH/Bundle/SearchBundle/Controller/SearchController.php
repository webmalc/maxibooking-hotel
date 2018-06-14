<?php

namespace MBH\Bundle\SearchBundle\Controller;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\ExpectedResult;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchController
 * @package MBH\Bundle\SearchBundle\Controller
 * @Route("/search")
 */
class SearchController extends Controller
{

    public const PRE_RESTRICTION_CHECK = true;

    /**
     * @Route("/")
     */
    public function searchTestAction()
    {
        $dm = $this->container->get('doctrine_mongodb.odm.default_document_manager');
        $roomType = $dm->getRepository(RoomType::class)->findOneBy(['fullTitle' => 'Номера комфорт в домиках 3-х местные']);
        $tariff = $dm->getRepository(Tariff::class)->findOneBy(['fullTitle' => 'Основной тариф']);
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('searchTime');

        $result = new ExpectedResult();
        $data = array (
            'begin' => '13.08.2018',
            'end' => '3.09.2018',
            'adults' => 2,
            'children' => 0,
            'additionalBegin' => 0,
            'roomTypes' => [],
            'tariffs' => []
        );

        

        try {
            $search = $this->get('mbh_search.search');
            $finded = $search->searchSync($data);
            $result
                ->setStatus('ok')
                ->setQueryHash($search->getSearchHash())
                ->setExpectedResults($search->getSearchCount())
            ;
        } catch (SearchQueryGeneratorException|SearchConditionException $e) {
            $result
                ->setStatus('error')
                ->setErrorMessage($e->getMessage())
                ->setExpectedResults(0)
            ;
        }

        $searchDone = $stopwatch->stop('searchTime');
        $time = $searchDone->getDuration();


        $filtered = [];
        foreach ($finded as $find) {
            if ($find['status'] === 'ok') {
                /** @var SearchResult $searchResult */
                $searchResult = $find['result'];
                $filtered[$searchResult->getRoomType()->getHotel()->getName() . '_' . $searchResult->getRoomType()->getName()][] = $searchResult;
            }
        }


        return $this->render('@MBHSearch/Search/index.html.twig', ['finded' => $finded, 'time' => $time, 'filtered' => $filtered]);
//        $serializer = $this->get('serializer');
//        return new Response(
//            $serializer->serialize($result, 'json'),
//            Response::HTTP_OK,
//            ['Content-Type' => 'application/json']
//        );
    }

    /**
     * @Route(
     *     "/json",
     *      name="search_start_json",
     *      condition="request.headers.get('Content-Type') matches '/application\\/json/i'"
     *     )
     * @param Request $request
     * @return Response
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     */
    public function searchRequestAction(Request $request): Response
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('searchTime');

        $result = new ExpectedResult();
        $data = json_decode($request->getContent(), true);

        $searchRequestReceiver = $this->get('mbh_search.search_request_receiver');
        $searchQueryGenerator = $this->get('mbh_search.search_query_generator');

        $conditions = $searchRequestReceiver->createSearchConditions($data);

        try {
            $searchQueries = $searchQueryGenerator->generateSearchQueries($conditions);
            $search = $this->get('mbh_search.search');
            $finded = $search->searchSync($searchQueries, $conditions);
            $result
                ->setStatus('ok')
                ->setQueryHash($search->getSearchHash())
                ->setExpectedResults($search->getSearchCount())
            ;
        } catch (SearchQueryGeneratorException $e) {
            $result
                ->setStatus('error')
                ->setErrorMessage($e->getMessage())
                ->setExpectedResults(0)
            ;
        }

        $searchDone = $stopwatch->stop('searchTime');
        $time = $searchDone->getDuration();
        return new JsonResponse($time);
//        $serializer = $this->get('serializer');
//        return new Response(
//            $serializer->serialize($result, 'json'),
//            Response::HTTP_OK,
//            ['Content-Type' => 'application/json']
//        );
    }

    /**
     * @Route("/results/{hash}", name="search_get_results")
     */
    public
    function searchResponseAction(
        Request $request
    ) {
        $result = ['search_result' => 5];

        return new JsonResponse($result);
    }
}
