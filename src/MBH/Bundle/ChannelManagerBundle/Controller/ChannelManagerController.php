<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class ChannelManagerController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     *
     * @Route("/package/notifications/{name}", name="channel_manager_notifications")
     * @Method({"POST"})
     * @Template()
     */
    public function packageNotificationsAction(Request $request, $name)
    {
        $services = $this->container->getParameter('mbh.channelmanager.services');

        if (!array_key_exists($name, $services)) {
            throw $this->createNotFoundException();
        }

        try {
            return $this->get($services[$name]['service'])->createPackages();
        } catch (\Exception $e){
            return new Response('ERROR');
        }

        return new Response('OK');
    }
}
