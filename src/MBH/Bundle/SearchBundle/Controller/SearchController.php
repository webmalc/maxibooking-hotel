<?php

namespace MBH\Bundle\SearchBundle\Controller;

use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\ExpectedResult;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 * @package MBH\Bundle\SearchBundle\Controller
 * @Route("/search")
 */
class SearchController extends Controller
{

    public const PRE_RESTRICTION_CHECK = true;

    /**
     * @Route(
     *     "/json",
     *      name="search_start_json",
     *      condition="request.headers.get('Content-Type') matches '/application\\/json/i'"
     *     )
     * @param Request $request
     * @return JsonResponse
     */
    public function searchRequestAction(Request $request): JsonResponse
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('searchTime');

        $result = new ExpectedResult();
        $data = json_decode($request->getContent(), true);

        $searchRequestReceiver = $this->get('mbh_search.search_request_receiver');
        $searchQueryGenerator = $this->get('mbh_search.search_query_generator');
        $restrictionChecker = $this->get('mbh_search.restrictions_checker_service');

        $conditions = $searchRequestReceiver->handleData($data);
        $searchQueries = $searchQueryGenerator->generateSearchQueries($conditions);
        $restrictionChecker->setConditions($conditions);

        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$restrictionChecker, 'check']);
        }


        $searcher = $this->get('mbh_search.searcher');
        foreach ($searchQueries as $searchQuery) {
            try {
                $results[] = $searcher->search($searchQuery);
            } catch (SearchException $e) {
                continue;
            }

        }
        $searchDone = $stopwatch->stop('searchTime');
        $time = $searchDone->getDuration();

        $a = 'b';
//            $result
//                ->setOkStatus()
//                ->setExpectedResults($searchQueryGenerator->getQueuesNum())
//                ->setQueryHash(
//                    $searchQueryGenerator->getSearchQueryHash()
//                );

        return new JsonResponse([$time]);
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
