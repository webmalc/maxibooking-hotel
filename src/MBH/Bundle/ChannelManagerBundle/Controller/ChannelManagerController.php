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
     * @Route("/package/notifications/{name}", name="channel_manager_notifications")
     * @Method({"POST"})
     * @param string $name
     * @return Response
     */
    public function packageNotificationsAction($name)
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

    /**
     * Sync rooms & tariffs
     *
     * @Route("/sync/{name}", name="channel_manager_sync")
     * @Method({"GET"})
     * @param string $name
     * @return Response
     */
    public function syncAction(Request $request, $name = null)
    {
        $this->get('mbh.channelmanager')->sync($name);

        if ($name) {
            $request->getSession()->getFlashBag()
                ->set('success', 'Синхронизация окончена.')
            ;
            return $this->redirect($this->generateUrl($name));
        }
        return new Response('OK');
    }
}
