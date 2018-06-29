<?php

namespace MBH\Bundle\SearchBundle\Controller;

use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
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

    //      condition="request.headers.get('Content-Type') matches '/application\\/json/i'"

    /**
     * @Route(
     *     "/sync/json",
     *      name="search_sync_start_json",
     *      options={"expose"=true}
     *     )
     */
    public function syncSearchAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $search = $this->get('mbh_search.search');
        try {
            if (!\is_array($data)) {
                throw new SearchConditionException('Bad received data');
            }
            $results = $search->searchSync($data);
            $json = json_encode(['results' => $results], JSON_NUMERIC_CHECK);
            $answer = new JsonResponse($json, 200, [], true);
        } catch (SearchConditionException|SearchQueryGeneratorException $e) {
            $answer = new JsonResponse(['error' => $e->getMessage()], 50);
        }

        return $answer;
    }

    /**
     * @Route(
     *     "/async/start",
     *      name="search_start_async",
     *      options={"expose"=true}
     *     )
     */
    public function asyncSearchAction(Request $request)
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('searchTime');

        $data = json_decode($request->getContent(), true);
        $search = $this->get('mbh_search.search');
        $search->setAsyncQueriesChunk(300);


        try {
            if (!\is_array($data)) {
                throw new SearchConditionException('Received bad data');
            }
            $conditionsId = $search->searchAsync($data);
            $answer = new JsonResponse(['conditionsId' => $conditionsId]);
        } catch (SearchConditionException|SearchQueryGeneratorException $e) {
            $answer = new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return $answer;
    }

    /**
     * @Route("/async/results/{id}" , name="search_async_results",  options={"expose"=true})
     * @param SearchConditions $conditions
     * @return JsonResponse
     */
    public function getAsyncResultsAction(SearchConditions $conditions): JsonResponse
    {
        $receiver = $this->get('mbh__search.async_result_receiver');
        try {
            $results = array_values($receiver->receive($conditions));
            $json = json_encode(['results' => $results]);
            $answer = new JsonResponse($json, 200, [], true);
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
        $begin = new \DateTime('10.09.2018');
        $end = new \DateTime('17.09.2018');
        $adults = 2;
        $conditions = new SearchConditions();
        $conditions->setBegin($begin)->setEnd($end)->setAdults($adults);
        $form = $this->createForm(SearchConditionsType::class, $conditions);
        return $this->render('@MBHSearch/Search/client.html.twig', ['form' => $form->createView()]);
    }
}
