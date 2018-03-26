<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\DashboardEntry;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Confirm dashboard message
     * @Route("/{id}/confirm", name="dashboard_confirm", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_DASHBOARD')")
     * @ParamConverter("entity", class="MBHClientBundle:DashboardEntry")
     */
    public function confirmAction(DashboardEntry $entry)
    {
        $entry->setConfirmedAt(new \DateTime());
        $this->dm->flush();

        return new JsonResponse(['success' => true]);
    }
}
