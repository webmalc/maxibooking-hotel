<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MyAllocator\phpsdk\src\Api\VendorSet;
use MyAllocator\phpsdk\src\Object\Auth;

/**
 * @Route("/myallocator")
 */
class MyAllocatorController extends Controller implements CheckHotelControllerInterface, EnvironmentInterface
{
    /**
     * Main configuration page
     * @Route("/", name="channels")
     * @Method("GET")
     * @Security("is_granted('ROLE_MYALLOCATOR')")
     * @Template()
     */
    public function indexAction()
    {
        $api = new VendorSet();
        $auth = new Auth();
        $auth->vendorId = 'maxipanev';
        $auth->vendorPassword = 'VCuMhanjdxiC';
        $api->setAuth($auth);
        $api->setParams(['Callback/URL' => 'http://google.ru122', 'Callback/Password' => 'sdsdsds']);

        //$api->setConfig('dataFormat', 'array');
        try {
            $rsp = $api->callApi();
        } catch (Exception $e) {
            $rsp = 'Oops: ' . $e->getMessage();
        }
        dump($rsp);

        return [
        ];
    }
}
