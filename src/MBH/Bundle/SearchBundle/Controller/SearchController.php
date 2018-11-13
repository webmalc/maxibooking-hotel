<?php

namespace MBH\Bundle\SearchBundle\Controller;

use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomTypesTypeException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SearchController
 * @package MBH\Bundle\SearchBundle\Controller
 * @Route("/")
 */
class SearchController extends Controller
{

    //      condition="request.headers.get('Content-Type') matches '/application\\/json/i'"

    /**
     * @Route(
     *     "/sync/json/{grouping}",
     *      name="search_sync_start_json",
     *      options={"expose"=true},
     *     defaults={"sorting" = null }
     *     )
     * @param Request $request
     * @param null|string $grouping
     * @return Response
     */
    public function syncSearchAction(Request $request, ?string $grouping = null): Response
    {
        $data = json_decode($request->getContent(), true);
        $search = $this->get('mbh_search.search');
        try {
            if (!\is_array($data)) {
                throw new SearchConditionException('Bad received data');
            }
            $json = $search->searchSync($data, $grouping, true, true);
            $answer = new JsonResponse($json, 200, [], true);
        } catch (SearchConditionException|SearchQueryGeneratorException|GroupingFactoryException $e) {
            $answer = new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return $answer;
    }

    /**
     * @Route(
     *     "/specials",
     *      name="search_specials",
     *      options={"expose"=true}
     *     )
     */
    public function specialSearchAction(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        try {
            $conditions = $this->get('mbh_search.search_condition_creator')->createSearchConditions($data);
        } catch (SearchConditionException $e) {
            return new JsonResponse('error in conditions', 500);
        }
        $specialSearcher = $this->get('mbh_search.special_search');
        $specials = $specialSearcher->search($conditions);

        return $this->render(
            '@MBHSearch/Search/specials.html.twig',
            [
                'specials' => $specials,
                'query' => $conditions
            ]
        );
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
        $data = json_decode($request->getContent(), true);
        $search = $this->get('mbh_search.search');
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
     * @Route("/async/results/{id}/{grouping}" , name="search_async_results",  options={"expose"=true}, defaults={"grouping" = null})
     * @param SearchConditions $conditions
     * @param null|string $grouping
     * @return JsonResponse
     * @throws GroupingFactoryException
     */
    public function getAsyncResultsAction(SearchConditions $conditions, ?string $grouping = null): JsonResponse
    {
        $receiver = $this->get('mbh_search.async_result_store');
        try {
            $json = $receiver->receive($conditions, $conditions->getErrorLevel(), $grouping, true, true);
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
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +1 days');
        $adults = 2;
        $children = 1;
        $childrenAges = [3];
        $conditions = new SearchConditions();
        $conditions->setBegin($begin)->setEnd($end)->setAdults($adults)->setChildren($children)->setChildrenAges($childrenAges);
        $form = $this->createForm(SearchConditionsType::class, $conditions);
        return $this->render('@MBHSearch/Search/client.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Uses direct from twig
     * @return Response
     */
    public function searcherAction(Request $request): Response
    {
        $initSearchConditions = new SearchConditions();
        $initSearchConditions
            ->setAdults(1)
            ->setChildren(0)
            ->setChildrenAges([]);

        $form = $this->createForm(SearchConditionsType::class, $initSearchConditions);
        try {
            $viewForm = $form->createView();
        } catch (RoomTypesTypeException $exception) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->set('error', $exception->getMessage());

            return new Response();
        }

        return $this->render('@MBHSearch/Search/searcher.html.twig', ['form' => $viewForm]);
    }

    /**
     * @Route("/cache/flush" , name="cache_flush",  options={"expose"=true})
     * @return JsonResponse
     */
    public function flushCacheAction(): Response
    {
        $cache = $this->get('mbh_search.search_cache_invalidator');
        $cache->flushCache();

        return new JsonResponse(['result' => 'Cache flushed']);
    }


    /**
     * @Route("/cache/invalidate/item/{id}/" , name="invalidate_item",  options={"expose"=true})
     * @param SearchResultCacheItem $cacheItem
     * @return Response
     */
    public function invalidateCacheItem(SearchResultCacheItem $cacheItem): Response
    {
        $service = $this->get('mbh_search.search_cache_invalidator');
        try {
            $service->invalidateCacheResultByCacheItem($cacheItem);
            $result = ['result' => 'Cache item was invalidated'];
        } catch (SearchResultCacheException $e) {
            $result = ['result' => 'Error while invalidate'];
        }

        return new JsonResponse($result);
    }

}
