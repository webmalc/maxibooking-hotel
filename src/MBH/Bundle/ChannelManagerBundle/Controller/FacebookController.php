<?php


namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class FacebookController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/facebook")
 */
class FacebookController extends Controller
{
    /**
     * Main configuration page
     * @Route("/", name="facebook")
     * @Method("GET")
     * @Security("is_granted('ROLE_FACEBOOK')")
     * @Template()
     */
    public function infoAction()
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteAddress = $siteManager->getSiteAddress();
        $searchResultAddress = $siteAddress ? $siteAddress . SiteManager::DEFAULT_RESULTS_PAGE : null;
        return [
            'searchResultAddress' => $searchResultAddress
        ];
    }
}