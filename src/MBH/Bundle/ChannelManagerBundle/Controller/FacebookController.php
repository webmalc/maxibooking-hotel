<?php


namespace MBH\Bundle\ChannelManagerBundle\Controller;


use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     */

    public function infoAction()
    {
        $siteManager = $this->get('mbh.site_manager');
        $searchResultAddress = $siteManager->getSiteAddress()? $siteManager->getSiteAddress() . SiteManager::DEFAULT_RESULTS_PAGE : null;
        return $this->render('MBHChannelManagerBundle:Facebook:info.html.twig',
            ['searchResultAddress' => $searchResultAddress]
        );
    }
}