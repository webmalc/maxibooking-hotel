<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dynamic/sales")
 * @Method("GET")
 *
 */
class DynamicSalesController extends Controller implements CheckHotelControllerInterface
{
    /**
     * DynamicSales
     *
     * @Route("/", name="dynamic_sales")
     * @Method("GET")
     * @Security("is_granted('ROLE_WINDOWS_REPORT')")
     * @Template()
     */
    public function indexAction()
    {
        $permissions = $this->container->get('mbh.package.permissions');

        $optionShowDynamicSales = [
            'dynamic.sales.day.sales.volume'=>'sales-volume',
            'dynamic.sales.period.sales.volume'=>'period-volume',
            'dynamic.sales.day.packages'=>'packages-sales',
            'dynamic.sales.day.packages.growth'=>'packages-growth',
            'dynamic.sales.day.sales.count.people'=>'count-people',
            'dynamic.sales.day.sales.count.room'=>'count-room'
        ];

        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'optionsShowDynamicSales' =>$optionShowDynamicSales
        ];
    }

    /**
     * Dynamic Sale table.
     *
     * @Route("/table", name="dynamic_sales_table", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_WINDOWS_REPORT')")
     * @Template()
     * @param $request Request
     * @return array
     */
    public function dynamicSalesTableAction(Request $request)
    {
        $hotel = $this->hotel;
        $optionsShows = empty($request->get('optionsShow')) ? [] : $request->get('optionsShow');

        $dynamicSales = $this->get('mbh.package.dynamic.sales.generator')->generateDynamicSales($request, $hotel);
        $error = false;

        (array_key_exists('error', $dynamicSales)) ? $error = $dynamicSales['error'] : null;

        return [
            'dynamicSales' => $dynamicSales,
            'error' => $error,
            'optionsShows' => $optionsShows,
        ];
    }
}