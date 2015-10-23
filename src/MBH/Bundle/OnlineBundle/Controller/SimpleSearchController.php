<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @Route("/simple/search")
 */
class SimpleSearchController extends Controller
{
    /**
     * @Route("/ajax/form", name="simple_search_ajax_form")
     * @Method({"GET", "POST"})
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:form.html.twig")
     */
    public function ajaxFormAction()
    {
        return [
            'highwayList' => $this->get('mbh.online.highwa_repository')->getList()
        ];
    }


    /**
     * @Route("/ajax/results", name="simple_search_ajax_results")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:results.html.twig")
     */
    public function ajaxResultsAction(Request $request)
    {
        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
        $query->distance = (float)$request->get('distance');
        $query->highway = $request->get('highway');
        $query->addRoomType($request->get('roomType'));

        $queryID = $request->get('query_id');

        if($request->get('query_type') == 'city') {
            $query->city = $queryID;
        } else {
            $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($queryID);
            if($hotel) {
                $query->addHotel($hotel);
            }
        };

        $searchResults = $this->get('mbh.package.search')->search($query);

        $results = [];
        foreach($searchResults as $result) {
            $hotelID = $result->getRoomType()->getHotel()->getId();
            if (!isset($results[$hotelID])) {
                $results[$hotelID] = [
                    'hotel' => $result->getRoomType()->getHotel(),
                    'roomTypes' => [$result->getRoomType()],
                    'result' => $result,
                ];
            } else {
                $results[$hotelID]['roomTypes'][] = $result->getRoomType();
            }
        }

        return [
            'results' => $results
        ];
    }

    /**
     * @Route("/ajax/detail/{id}", name="simple_search_ajax_detail")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:detail.html.twig")
     * @ParamConverter(class="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    public function ajaxDetailAction(Hotel $hotel, Request $request)
    {
        $photos = [];
        foreach($hotel->getRoomTypes() as $roomType) {
            foreach($roomType->getImages() as $image) {
                $photos[] = $image->getPath();
            }
        }

        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        $orders = $orderRepository->findByHotel($hotel);

        /** @var Order[] $orders */
        $orders = array_filter(iterator_to_array($orders),  function($order){ return count($order->getPollQuestions()) > 0; });

        $hotel->setRate($orderRepository->getRateByOrders($orders));

        $this->dm->persist($hotel);
        $this->dm->flush();

        $path = $this->get('file_locator')->locate('@MBHOnlineBundle/Resources/fixture/Autotravel_waypoints.gpx.txt');
        $simpleXmlElement = simplexml_load_string(file_get_contents($path));
        $sights = [];

        $leftBorder = [55.752757, 37.583895];
        $rightBorder = [55.750938, 37.655320];

        $topBorder = [55.773327, 37.620738];
        $bottomBorder = [55.730658, 37.621779];

        foreach($simpleXmlElement->children() as $child) {
            $showPlace = [
                'name' => (string) $child->name,
                'desc' => (string) '',//$child->desc,
                'lon' => (float) $child->attributes()->lon,
                'lat' => (float) $child->attributes()->lat
            ];

            if(
                ($showPlace['lon'] < $leftBorder[1] || $showPlace['lon'] > $rightBorder[1]) &&
                ($showPlace['lat'] > $topBorder[0] || $showPlace['lat'] < $bottomBorder[0])
            ) {
                $sights[] = $showPlace;
            }
        };

        return [
            'hotel' => $hotel,
            'photos' => $photos,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'orders' => $orders,
            'sights' => $sights,
        ];
    }

    /**
     * @Route("/ajax/map", name="simple_search_ajax_map")
     * @Method("GET")
     * @Template(template="MBHOnlineBundle:SimpleSearch/content:map.html.twig")
     */
    public function ajaxMapAction(Request $request)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findBy([
            'latitude' => ['$exists' => 1],
            'longitude' => ['$exists' => 1]
        ]);

        return [
            'hotels' => $hotels
        ];
    }

    /**
     * @Route("/search/{query}", name="simple_search", defaults={"query"=""})
     * @Method("GET")
     * @Template()
     */
    public function searchAction($query)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder()
            ->field('fullTitle')
            ->equals(new \MongoRegex('/.*'.$query.'.*/i'))
            ->getQuery()->execute()
        ;

        $cities = $this->dm->getRepository('MBHHotelBundle:City')
            ->createQueryBuilder()
            ->field('title')
            ->equals(new \MongoRegex('/.*'.$query.'.*/i'))
            ->getQuery()->execute()
        ;

        $response = [];

        foreach($hotels as $hotel) {
            $response[] = [
                'id' => $hotel->getId(),
                'name' => $hotel->getFullTitle(),
                'type' => 'hotel'
            ];
        }
        foreach($cities as $city) {
            $response[] = [
                'id' => $city->getId(),
                'name' => $city->getTitle(),
                'type' => 'city'
            ];
        }

        return new JsonResponse($response);
    }


    /**
     * @param Request $request
     * @return \Guzzle\Http\EntityBodyInterface|string
     */
    private function getSearchFormHtml(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $formUrl = $this->generateUrl('simple_search_ajax_form', $request->query->all(), UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @return \Guzzle\Http\EntityBodyInterface|string
     */
    private function getDetailContent(Request $request, Hotel $hotel)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $parameters['id'] = $hotel->getId();
        $formUrl = $this->generateUrl('simple_search_ajax_detail', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    private function getResultsContent(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $formUrl = $this->generateUrl('simple_search_ajax_results', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    private function getMapContent(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');

        $parameters = $request->query->all();
        $formUrl = $this->generateUrl('simple_search_ajax_map', $parameters, UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        return $formResponse->getBody();
    }

    /**
     * @Route("/index", name="simple_search_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getResultsContent($request)
        ];
    }

    /**
     * @Route("/view/{id}", name="simple_search_view")
     * @Method("GET")
     * @Template()
     * @ParamConverter(class="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    public function viewAction(Hotel $hotel, Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getDetailContent($request, $hotel)
        ];
    }

    /**
     * @Route("/map", name="simple_search_map")
     * @Method("GET")
     * @Template()
     */
    public function mapAction(Request $request)
    {
        return [
            'form' => $this->getSearchFormHtml($request),
            'content' => $this->getMapContent($request)
        ];
    }
}