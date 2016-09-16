<?php


namespace MBH\Bundle\ChannelManagerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ChannelManagerBundle\Form\HundredOneHotelType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HundredOneHotelsController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 * @Route("/hundredOneHotels")
 */
class HundredOneHotelsController extends Controller
{
    /**
     * Main configuration page
     * @Route("/", name="hundred_one_hotels")
     * @Method("GET")
     * @Security("is_granted('ROLE_BOOKING')")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->hotel->getHundredOneHotelsConfig();

        $form = $this->createForm(HundredOneHotelType::class, $config);

        return [
            'form' => $form->createView(),
            'config' => $config,
            'logs' => $this->logs($config)
        ];
    }

//    /**
//     * @Template()
//     * @Route("/test")
//     */
//    public function testAction()
//    {
//        $arr[0][1] = 'opop';
//        return [
//            'arr' => $arr
//        ];
//    }

    /**
     * @Route("/", name="hundred_one_hotels_save")
     * @Method("POST")
     * @Template("MBHChannelManagerBundle:HundredOneHotels:index.html.twig")
     * @param Request $request
     * @return array
     */
    public function saveAction(Request $request)
    {
    }
}