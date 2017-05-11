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
 * @Route("/dynamic_sales")
 * @Method("GET")
 */
class DynamicSalesController extends Controller implements CheckHotelControllerInterface
{
    const DYNAMIC_SALES_SHOWN_OPTIONS = [
        'sales-volume',
        'period-volume',
        'packages-sales',
        'packages-growth',
        'count-people',
        'count-room',
        'package-isPaid',
        'package-isPaid-growth',
        'package-delete',
        'package-delete-price',
        'package-delete-price-growth',
        'package-delete-price-isPaid',
        'package-isPaid-delete-package',
        'count-people-day',
        'count-room-day',
        'sum-payed-for-period',
        'sum-payed-for-period-for-removed'
    ];

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
            'optionsShowDynamicSales' => self::DYNAMIC_SALES_SHOWN_OPTIONS
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
        $optionsShows = empty($request->get('optionsShow')) ? self::DYNAMIC_SALES_SHOWN_OPTIONS : $request->get('optionsShow');

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