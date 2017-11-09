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
     * @Route("/table", name="dynamic_sales_table", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_DYNAMIC_SALES')")
     * @Template()
     * @param $request Request
     * @return array
     */
    public function dynamicSalesTableAction(Request $request)
    {
        $requestOptions = $this->helper->getDataFromMultipleSelectField($request->get('optionsShow'));
        $displayedOptions = empty($requestOptions) ? DynamicSales::DYNAMIC_SALES_SHOWN_OPTIONS : $requestOptions;

        $filterBeginDates = $this->helper->getDataFromMultipleSelectField($request->get('begin'));
        $filterEndDates = $this->helper->getDataFromMultipleSelectField($request->get('end'));
        $roomTypeOptions = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));

        if (!empty($roomTypeOptions) && !(count($roomTypeOptions) == 1 && current($roomTypeOptions) == 'total')) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($this->hotel, $request->get('roomTypes'));
        } else {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $this->hotel->getId()]);
        }

        $dynamicSalesReportData = $this->get('mbh.package.dynamic.sales.generator')
            ->getDynamicSalesReportData($filterBeginDates, $filterEndDates, $roomTypes);

        return [
            'roomTypeOptions' => $roomTypeOptions,
            'dynamicSalesData' => $dynamicSalesReportData,
            'optionsShows' => $displayedOptions,
        ];
    }
}