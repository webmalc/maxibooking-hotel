<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\ChannelManagerBundle\Form\AirbnbType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/airbnb")
 * Class AirbnbController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 */
class AirbnbController extends BaseController
{
    /**
     * @Route("/", name="airbnb")
     * @Method("GET")
     * @Security("is_granted('ROLE_AIRBNB')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getAirbnbConfig();

//        $isReadyResult = $this->get('mbh.cm_wizard_manager')->checkForReadinessOrGetStepUrl($config, 'booking');
//        if ($isReadyResult !== true) {
//            return $this->redirect($isReadyResult);
//        }

        $form = $this->createForm(
            AirbnbType::class,
            $config
        );

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config)
        ];
    }
}