<?php

namespace MBH\Bundle\SiteBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("config")
 */
class ConfigController extends Controller {

    /**
     * Site config.
     *
     * @Route("/form", name="site_config")
     * @Security("is_granted('ROLE_SITE_CONFIG')")
     * @Method("GET")
     * @Template()
     */
    public function formAction()
    {
        return [
        ];
    }
}
