<?php

namespace MBH\Bundle\RestaurantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class DishMenuController
 * @package MBH\Bundle\RestaurantBundle\Controller
 * @Route("dishmenu")
 */
class DishMenuController extends Controller
{
    /**
     * @Route("/", name="restaurant_dishmenu_list")
     * @Template()
     */
    public function listAction()
    {
        return array(
                // ...X
            );    }

}
