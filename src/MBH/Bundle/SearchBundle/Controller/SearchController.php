<?php

namespace MBH\Bundle\SearchBundle\Controller;

use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
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

    //      condition="request.headers.get('Content-Type') matches '/application\\/json/i'"

    /**
     * @Route(
     *     "/json",
     *      name="search_start_json",
     *      options={"expose"=true}
     *     )
     */
    public function asyncSearchAction(Request $request)
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('searchTime');

        $data = json_decode($request->getContent(), true);
        $search = $this->get('mbh_search.search');
        $search->setAsyncQueriesChunk(200);


        try {
            if (!is_array($data)) {
                throw new SearchConditionException('Bad received data');
            }
            $conditionsId = $search->searchAsync($data);
            $answer = new JsonResponse(['conditionsId' => $conditionsId]);
        } catch (SearchConditionException|SearchQueryGeneratorException $e) {
            $answer = new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return $answer;
    }

    /**
     * @Route("/results/{id}" , name="search_async_results",  options={"expose"=true})
     * @param SearchConditions $conditions
     * @return JsonResponse
     */
    public function getAsyncResultsAction(SearchConditions $conditions): JsonResponse
    {
        $receiver = $this->get('mbh__search.async_result_receiver');
        try {
            $results = $receiver->receive($conditions);
            $answer = new JsonResponse(['results' => $results]);
        } catch (AsyncResultReceiverException $exception) {
            $answer = new JsonResponse(['results' => [], 'message' => $exception->getMessage()], 204);
        }

        return $answer;
    }

    /**
     * @Route("/client", name="search_client")
     */
    public function clientAction(): Response
    {
        return $this->render('@MBHSearch/Search/client.html.twig');
    }
}
