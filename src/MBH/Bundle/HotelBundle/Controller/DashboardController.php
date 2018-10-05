<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_DASHBOARD')")
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