<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
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
     * @Route("/form", name="simple_search_form")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function formAction()
    {
        /*$formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);
        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }*/

        return [
            //'formConfig' => $formConfig
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
     * @Route("/detail", name="simple_search_detail")
     * @Method("GET")
     * @Template()
     */
    public function detailAction()
    {
        return [];
    }

    /**
     * @Route("/index", name="simple_search_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $guzzleClient = $this->get('guzzle.client');
        $formUrl = $this->generateUrl('simple_search_form', $request->query->all(), UrlGenerator::ABSOLUTE_URL);
        $formRequest = $guzzleClient->get($formUrl);
        $formResponse = $formRequest->send();

        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->isOnline = true;
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');
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
            'form' => $formResponse->getBody(),
            'results' => $results
            //'detail' => $detailResponse->getBody()
        ];
    }

    /**
     * @Route("/view", name="simple_search_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAction()
    {
        return [];
    }
}