<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * Index template
     * @Route("/", name="online_booking_index")
     */
    public function indexAction()
    {
        return $this->render('MBHOnlineBookingBundle:Default:index.html.twig', array('name' => '1'));
    }
}
