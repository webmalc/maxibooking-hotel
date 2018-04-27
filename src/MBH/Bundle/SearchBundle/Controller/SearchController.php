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
        $result = new ExpectedResult();
        try {
            $data = json_decode($request->getContent(), true);
            $searchRequestReceiver = $this->get('mbh_search.search_request_receiver');
            $conditions = $searchRequestReceiver->handleData($data);
            $searchQueryGenerator = $this->get('mbh_search.search_query_generator');
            $searchQueries = $searchQueryGenerator->generateSearchQueries($conditions);
            $restrictionChecker = $this->get('mbh_search.restrictions_checker_service');

            if (self::PRE_RESTRICTION_CHECK) {
                $searchQueries = array_filter($searchQueries, [$restrictionChecker, 'check']);
            }




//            $result
//                ->setOkStatus()
//                ->setExpectedResults($searchQueryGenerator->getQueuesNum())
//                ->setQueryHash(
//                    $searchQueryGenerator->getSearchQueryHash()
//                );
        } catch (SearchException $e) {
            $result->setErrorStatus()->setErrorMessage($e->getMessage());
        }

        return new JsonResponse($result);
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
