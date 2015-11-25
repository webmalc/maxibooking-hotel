<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /**
     * @Route("/form", name="online_booking_form")
     */
    public function formAction()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        /** @var RoomTypeRepositoryInterface $roomTypeRepository */
        $roomTypeRepository = $this->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository->findAll();

        $requestSearchUrl = $this->getParameter('online_booking')['request_search_url'];

        return $this->render('MBHOnlineBookingBundle:Default:form.html.twig', [
            'hotels' => $hotels,
            'roomTypes' => $roomTypes,
            'requestSearchUrl' => $requestSearchUrl
        ]);
    }

    /**
     * @Route("/search", name="online_booking_search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();
        $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($request->get('hotel'));
        if($hotel) {
            $searchQuery->addHotel($hotel);
        }
        if($request->get('roomType')) {
            $searchQuery->addRoomType($request->get('roomType'));
        }
        $helper = $this->get('mbh.helper');
        $searchQuery->begin = $helper->getDateFromString($request->get('begin'));
        $searchQuery->end = $helper->getDateFromString($request->get('end'));
        $searchQuery->adults = $request->get('adults');
        $searchQuery->children = $request->get('children');

        $searchResults = $this->get('mbh.package.search')->search($searchQuery);

        return [
            'searchResults' => $searchResults
        ];
    }
}
