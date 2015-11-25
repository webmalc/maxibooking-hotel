<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/")
 */
class DefaultController extends BaseController
{
    /**
     * Index template
     * @Route("/", name="online_booking_index")
     */
    public function indexAction()
    {
        return $this->render('MBHOnlineBookingBundle:Default:index.html.twig', array('name' => '1'));
    }

    /**
     * @Route("/form", name="online_booking_form")
     */
    public function formAction()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        /** @var RoomTypeRepositoryInterface $roomTypeRepository */
        $roomTypeRepository = $this->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository->findAll();
        return $this->render('MBHOnlineBookingBundle:Default:form.html.twig', [
            'hotels' => $hotels,
            'roomTypes' => $roomTypes
        ]);
    }

    public function searchAction(Request $request)
    {
        $searchQuery = new SearchQuery();
        //$searchQuery->
        $searchResaults = $this->get('mbh.package.search')->search($searchQuery);

        return [];
    }
}
