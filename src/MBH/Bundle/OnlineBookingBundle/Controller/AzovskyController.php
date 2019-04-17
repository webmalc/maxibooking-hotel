<?php


namespace MBH\Bundle\OnlineBookingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AzovskyController
 * @Route("/azovsky")
 */
class AzovskyController extends Controller
{
    /**
     * @return Response
     * @Route("/results")
     */
    public function azovskyResultsAction(): Response
    {
        return $this->render('@MBHOnlineBooking/Azovsky/results.js.twig');
    }
}