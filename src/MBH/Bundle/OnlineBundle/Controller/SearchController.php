<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/search")
 */
class SearchController extends Controller
{

    /**
     * Form action
     * @Route("/form", name="online_form_show")
     * @Method("GET")
     * @Template()
     */
    public function formAction()
    {
        return [];
    }
}
