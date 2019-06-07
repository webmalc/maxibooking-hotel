<?php

namespace MBH\Bundle\SearchBundle\Controller;

use JoliCode\Slack\ClientFactory;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Form\RoomTypesType;
use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Events\SearchEvent;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomTypesTypeException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
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
     *     "/async/start",
     *      name="search_start_async",
     *      options={"expose"=true}
     *     )
     */
    public function asyncSearchAction(Request $request): JsonResponse
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
            $json = $receiver->receive($conditions, $grouping, true, true);
            $answer = new JsonResponse($json, 200, [], true);
        } catch (AsyncResultReceiverException $exception) {
            $answer = new JsonResponse(['results' => [], 'message' => $exception->getMessage()], 204);
            $event = new SearchEvent();
            $event->setSearchConditions($conditions);
            $this->get('event_dispatcher')->dispatch(SearchEvent::SEARCH_ASYNC_END, $event);
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
     * Search action
     * @Route("/", name="package_new_search", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_SEARCH')")
     **/

    public function searcherAction(Request $request): Response
    {
        $orderId = $request->get('order');
        try {
            $roomTypeForm = $this->createForm(RoomTypesType::class)->createView();
        } catch (RoomTypesTypeException $e) {
            $flashBag = $request->getSession()->getFlashBag();
            /** @var FlashBag $flashBag */
            $flashBag->set('error', $e->getMessage());
            return $this->redirectToRoute('hotel');
        }
        $formChoices = $roomTypeForm->vars['choices'];


        $choices = [];
        foreach ($formChoices as $groupChoice) {
            /** @var ChoiceGroupView $groupChoice */
            /** @var ChoiceView $choice */
            $children = [];
            foreach ($groupChoice->getIterator() as $choice) {
                $children[] = [
                    'id' => $choice->value,
                    'text' => $choice->label,
                ];
            }
            $choices[] = [
                'text' => $groupChoice->label,
                'children' => $children,
            ];
        }

        $config = $this->get('doctrine.odm.mongodb.document_manager')->getRepository(ClientConfig::class)->findOneBy([]);
        /** @var ClientConfig $config */
        $begin = $config->getBeginDate();
        if (!$begin) {
            $begin = new \DateTime('midnight');
        }
        $end = (clone $begin)->modify('+7 days');

        return $this->render('@MBHSearch/Search/searcher.html.twig', [
            'order' => $orderId,
            'begin' => $begin,
            'end' => $end,
            'roomTypes' => $choices
        ]);
    }


    /**
     * @Route("/price-check", name="search_price_check", options={"expose"=true}, defaults={"_format":"json"})
     * @Security("is_granted('ROLE_SEARCH')")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \MBH\Bundle\SearchBundle\Services\Search\Debug\DebugPriceCheckerException
     */
    public function priceCheckAction(Request $request): JsonResponse
    {
        $checker = $this->get('mbh_search.debug_price_checker');
        $result = $checker->checkPrices($request);

        return new  JsonResponse(['wrongPrice' => $result]);
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
