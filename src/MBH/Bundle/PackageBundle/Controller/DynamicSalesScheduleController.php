<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/dynamicSalesSchedule")
 */
class DynamicSalesScheduleController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Index template
     * @Route("/", name="dynamicSalesSchedule")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template()
     */
    public function indexAction()
    {
        $optionShowDynamicSales = [
            'dynamic.sales.schedule.sales.day' => 'sales-day',
            'dynamic.sales.schedule.sales.amount' => 'count-packages',
            'dynamic.sales.schedule.sales.count.people' => 'count-people',
            'dynamic.sales.schedule.sales.count.numbers' => 'count-numbers',

        ];

        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'optionsShowDynamicSales' => $optionShowDynamicSales
        ];
    }

    /**
     * Dynamic Sale table.
     *
     * @Route("/response", name="dynamic_sales_schedule", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_WINDOWS_REPORT')")
     * @Template()
     * @param $request Request
     * @return array
     */
    public function responseAction(Request $request)

    {
        $hotel = $this->hotel;
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $dynamicSalesSchedule = $this->get('mbh.package.dynamic.sales.schedule')->generateDynamicSales($request, $hotel);
        $jsonContent = $serializer->serialize($dynamicSalesSchedule, 'json');

        $response = new Response();
        $response->setContent($jsonContent);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
