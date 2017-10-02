<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Lib\DynamicSales;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dynamic_sales")
 * @Method("GET")
 */
class DynamicSalesController extends Controller implements CheckHotelControllerInterface
{
    /**
     * DynamicSales
     *
     * @Route("/", name="dynamic_sales")
     * @Method("GET")
     * @Security("is_granted('ROLE_DYNAMIC_SALES')")
     * @Template()
     */
    public function indexAction()
    {
        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'optionsShowDynamicSales' => DynamicSales::DYNAMIC_SALES_SHOWN_OPTIONS
        ];
    }

    /**
     * Dynamic Sale table.
     *
     * @Route("/table", name="dynamic_sales_table", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DYNAMIC_SALES')")
     * @Template()
     * @param $request Request
     * @return array
     */
    public function dynamicSalesTableAction(Request $request)
    {
        $hotel = $this->hotel;
        $requestOptions = $request->get('optionsShow');
        if (empty($requestOptions) || !is_array($requestOptions) || $requestOptions[0] == '') {
            $displayedOptions = DynamicSales::DYNAMIC_SALES_SHOWN_OPTIONS;
        } else {
            $displayedOptions = $requestOptions;
        }

        $dynamicSalesReportData = $this->get('mbh.package.dynamic.sales.generator')->generateDynamicSales($request, $hotel);

        return [
            'dynamicSalesData' => $dynamicSalesReportData,
            'optionsShows' => $displayedOptions,
        ];
    }
}