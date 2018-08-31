<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * Class DashboardController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="dashboard")
     * @Template()
     */
    public function indexAction()
    {

    }
}
