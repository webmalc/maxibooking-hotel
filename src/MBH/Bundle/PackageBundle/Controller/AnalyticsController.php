<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\UserBundle\Document\User;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/analytics")
 */
class AnalyticsController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Index template
     * @Route("/", name="analytics")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template()
     */
    public function indexAction()
    {
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->sort('hotel.id', 'asc')
            ->getQuery()
            ->execute();

        return [
            'types' => $this->container->getParameter('mbh.analytics.types'),
            'roomTypes' => $roomTypes,
        ];
    }

    /**
     * Index template
     * @Route("/choose", name="analytics_choose", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function chooseAction(Request $request)
    {
        foreach ($this->container->getParameter('mbh.analytics.types') as $key => $type) {
            if ($key == $request->get('type')) {
                try {
                    return $this->redirect($this->generateUrl('analytics_' . $key, $request->query->all()));
                } catch (\Exception $e) {
                    return new JsonResponse(['error' => true]);
                }
            }
        }

        return new JsonResponse(['result' => true]);
    }

    /**
     * @Route("/sales_services", name="analytics_sales_services")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesServicesAction()
    {
        $data = [];
        $packages = $this->getPackages()->toArray();
        $packageServicesByPackageIds = $this->getPackageServicesByPackageIds($packages);

        foreach ($this->getPackages() as $package) {
            $packageServices = isset($packageServicesByPackageIds[$package->getId()])
                ? $packageServicesByPackageIds[$package->getId()]
                : [];
            /** @var PackageService $packageService */
            foreach ($packageServices as $packageService) {
                $id = $packageService->getService()->getId();
                $day = $package->getCreatedAt()->format('d.m.Y');
                $month = $package->getCreatedAt()->format('m.Y');

                if (!isset($data[$id][$day])) {
                    $data[$id][$day] = 0;
                }
                if (!isset($data[$id][$month])) {
                    $data[$id][$month] = 0;
                }

                $data[$id][$day] += $packageService->getAmount();
                $data[$id][$month] += $packageService->getAmount();
            }
        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.services_amount'));
        $chart->series($this->getSeries($data, 'getServices'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_cash_documents", name="analytics_sales_cash_documents")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesCashDocumentsAction()
    {
        $data = $ids = [];
        /** @var Package[] $packages */
        $packages = $this->getPackages()->toArray();
        $cashDocumentsByOrdersIds = $this->getPackagesCashDocumentsByOrdersIds($packages);

        foreach ($packages as $package) {
            $id = $package->getRoomType()->getId();
            $cashDocuments = isset($cashDocumentsByOrdersIds[$package->getOrder()->getId()])
                ? $cashDocumentsByOrdersIds[$package->getOrder()->getId()]
                : [];

            /** @var CashDocument $cashDocument */
            foreach ($cashDocuments as $cashDocument) {

                if (in_array($cashDocument->getId(), $ids)) {
                    continue;
                }
                $ids[] = $cashDocument->getId();

                $day = $cashDocument->getCreatedAt()->format('d.m.Y');
                $month = $cashDocument->getCreatedAt()->format('m.Y');

                if (!isset($data[$id][$day])) {
                    $data[$id][$day] = 0;
                }
                if (!isset($data[$id][$month])) {
                    $data[$id][$month] = 0;
                }

                if ($cashDocument->getOperation() == 'in') {
                    $data[$id][$day] += $cashDocument->getTotal();
                    $data[$id][$month] += $cashDocument->getTotal();
                } else {
                    $data[$id][$day] -= $cashDocument->getTotal();
                    $data[$id][$month] -= $cashDocument->getTotal();
                }
            }
        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.proceeds'));
        $chart->series($this->getSeries($data));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/hotel_occupancy", name="analytics_hotel_occupancy")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function hotelOccupancyAction()
    {
        $data = $newData = $total = [];

        foreach ($this->getPackages() as $package) {
            $id = $package->getRoomType()->getId();

            if (!isset($total[$id])) {
                $total[$id] = count($package->getRoomType()->getRooms());
            }

            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day]++;
            $data[$id][$month]++;
        }

        foreach ($data as $id => $values) {
            if (!isset($total[$id])) {
                continue;
            }
            foreach ($values as $dataId => $value) {
                $newData[$id][$dataId] = round($value / $total[$id], 2) * 100;
            }
        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.percentage'));
        $chart->series($this->getSeries($newData, 'getRoomTypes', true));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_managers", name="analytics_sales_managers")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesManagersAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {

            $username = $package->getCreatedBy();

            if (empty($username)) {
                continue;
            }

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $manager = $dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => $username]);

            if (empty($manager)) {
                continue;
            }

            $id = $manager->getId();
            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day]++;
            $data[$id][$month]++;

        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.package_trips_amount'));
        $chart->series($this->getSeries($data, 'getManagers'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_sources", name="analytics_sales_sources")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesSourcesAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $source = $package->getSource();
            if (!$source) {
                continue;
            }

            $id = $source->getId();
            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day]++;
            $data[$id][$month]++;

        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.package_trips_amount'));
        $chart->series($this->getSeries($data, 'getSources'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_packages", name="analytics_sales_packages")
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesPackagesAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $id = $package->getRoomType()->getId();
            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day]++;
            $data[$id][$month]++;
        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.package_trips_amount'));
        $chart->series($this->getSeries($data));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_cash", name="analytics_sales_cash", defaults={"_format"="json"})
     * @Method("GET")
     * @Security("is_granted('ROLE_ANALYTICS')")
     * @Template()
     */
    public function salesCashAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $id = $package->getRoomType()->getId();
            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day] += $package->getPrice();
            $data[$id][$month] += $package->getPrice();
        }

        $chart = $this->getChart($this->get('translator')->trans('controller.analyticsController.proceeds'));
        $chart->series($this->getSeries($data));

        return $this->getResponse($chart);
    }

    /**
     * @param string $y
     * @return Highchart
     */
    private function getChart($y)
    {
        $request = $this->getRequest();
        $chart = new Highchart();
        if (!$request->get('type')) {
            return $chart;
        }

        if ($request->get('months')) {
            $chart->chart->type('column');
        }
        $chart->chart->renderTo('analytics_filter_content');
        $chart->title->text($this->get('translator')->trans('mbh.analytics.types.' . $request->get('type')));
        $chart->yAxis->title(['text' => $y]);
        $chart->xAxis->title(['text' => $this->get('translator')->trans('controller.analyticsController.sale_date')]);
        $chart->xAxis->type('datetime');
        $chart->xAxis->dateTimeLabelFormats(['month' => '%e. %b', 'year' => '%b']);
        $chart->chart->zoomType('x');
        $chart->tooltip->formatter('@function(){ return highchartsTooltip(this.series.name,this.x,this.y)}@');

        return $chart;
    }

    /**
     * @param Highchart $chart
     * @return JsonResponse
     */
    private function getResponse(Highchart $chart)
    {
        return new JsonResponse([
            'error' => null,
            'html' => $this->renderView('MBHPackageBundle:Analytics:response.html.twig', ['chart' => $chart]),
        ]);
    }

    /**
     * @return \DatePeriod
     */
    private function getInterval()
    {
        $request = $this->getRequest();

        $begin = $end = null;

        if (!empty($request->get('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('begin') . ' 00:00:00');
        }
        if (!empty($request->get('end'))) {
            $end = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('end') . ' 00:00:00');
        }
        if (!$end) {
            $end = new \DateTime();
            $end->setTime(0, 0, 0);
        };
        if (!$begin) {
            $begin = clone $end;
            $begin->modify('-1 month');
        }

        $end->modify('+1 day');

        return new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
    }

    private function getSources()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $dm->getRepository('MBHPackageBundle:PackageSource')->createQueryBuilder('s')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();
    }

    private function getServices()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $availableHotels = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->findAll();
        $availableHotelsIds = $this->helper->toIds($availableHotels);

        $categories = $this->dm
            ->getRepository('MBHPriceBundle:ServiceCategory')
            ->createQueryBuilder()
            ->field('hotel.id')->in($availableHotelsIds)
            ->getQuery()
            ->execute()
            ->toArray();

        $categoriesIds = $this->helper->toIds($categories);

        return $dm
            ->getRepository('MBHPriceBundle:Service')
            ->createQueryBuilder()
            ->field('category.id')->in($categoriesIds)
            ->sort(['hotel.id' => 'asc', 'fullName' => 'desc'])
            ->getQuery()
            ->execute();
    }

    private function getManagers()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $dm->getRepository('MBHUserBundle:User')->createQueryBuilder('s')
            ->sort(['lastName' => 'desc', 'username' => 'desc'])
            ->getQuery()
            ->execute();
    }

    /**
     * @param boolean $asIds
     * @return array
     */
    private function getRoomTypes($asIds = false)
    {
        $request = $this->getRequest();
        $requestedRoomTypesIds = $this->helper->getDataFromMultipleSelectField($request->get('roomType'));
        if (empty($requestedRoomTypesIds)) {
            $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
            foreach ($hotels as $hotel) {
                $requestedRoomTypesIds[] = 'total_' . $hotel->getId();
            }
        }
        $roomTypesIds = [];
        if (!empty($requestedRoomTypesIds)) {
            foreach ($requestedRoomTypesIds as $id) {
                if (mb_stripos($id, 'allrooms_') !== false || mb_stripos($id, 'total_') !== false) {
                    $hotelId = mb_stripos($id, 'allrooms_') !== false
                        ? str_replace('allrooms_', '', $id)
                        : str_replace('total_', '', $id);

                    /** @var Hotel $hotel */
                    $hotel = $this->dm->getRepository('MBHHotelBundle:Hotel')->find($hotelId);

                    if (!$hotel) {
                        continue;
                    }
                    foreach ($hotel->getRoomTypes() as $roomType) {
                        $roomTypesIds[] = $roomType->getId();
                    }
                } else {
                    $roomTypesIds[] = $id;
                }
            }
            $roomTypesIds = array_unique($roomTypesIds);
        }

        if ($asIds) {
            return $roomTypesIds;
        }
        $qb = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->sort(['hotel.id' => 'asc', 'fullName' => 'desc']);

        if (count($roomTypesIds)) {
            $qb->field('id')->in($roomTypesIds);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param object $category
     * @return string
     */
    private function getCategoryName($category)
    {
        if ($category instanceof User) {
            return $category->getFullName(true);
        }
        if ($category instanceof Service) {
            return $category->getCategory()->getHotel() . ': ' . $category;
        }
        if ($category instanceof RoomType) {
            return $category->getHotel() . ': ' . $category;
        }
        if (method_exists($category, 'getName')) {
            return $category->getName();
        }

        return (string)$category;
    }

    /**
     * @param array $values
     * @param string $categoryGetMethod
     * @param boolean $withoutTotal
     * @return array
     */
    private function getSeries($values = array(), $categoryGetMethod = 'getRoomTypes', $withoutTotal = false)
    {
        $request = $this->getRequest();
        $cumulative = $request->get('cumulative');
        $months = $request->get('months');
        $requestedRoomTypes = $this->helper->getDataFromMultipleSelectField($request->get('roomType'));
        if (empty($requestedRoomTypes)) {
            $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
            foreach ($hotels as $hotel) {
                $requestedRoomTypes[] = 'total_' . $hotel->getId();
            }
        }
        $series = $totalValues = $allValues = [];
        $i = 0;
        if ($categoryGetMethod === 'getRoomTypes') {
            $roomTypes = $this->$categoryGetMethod()->toArray();
            $numberOfRoomsByHotels = [];
            /** @var RoomType $roomType */
            foreach ($roomTypes as $roomType) {
                isset($numberOfRoomsByHotels[$roomType->getHotel()->getId()])
                    ? $numberOfRoomsByHotels[$roomType->getHotel()->getId()]+= $roomType->getRooms()->count()
                    : $numberOfRoomsByHotels[$roomType->getHotel()->getId()] =  $roomType->getRooms()->count();
            }
        }
        foreach ($this->$categoryGetMethod() as $category) {
            if ($this->isDisplayedCategory($category, $requestedRoomTypes, $categoryGetMethod)) {
                $series[$i]['name'] = $this->getCategoryName($category);
            }
            /** @var \DateTime $date */
            foreach ($this->getInterval() as $date) {
                $value = 0;
                $prev = clone $date;
                $prev->modify('-1 day');
                $prevId = $prev->format('d.m.Y');
                $dayId = $date->format('d.m.Y');

                if ($months && $date->format('j') == 15) {
                    $dayId = $date->format('m.Y');
                    $prev = clone $date;
                    $prev->modify('-1 month');
                    $prevId = $prev->format('m.Y');
                }

                if (isset($values[$category->getId()][$dayId])) {
                    $value = $values[$category->getId()][$dayId];
                }

                if ($cumulative && isset($allValues[$category->getId()][$prevId])) {
                    $value += $allValues[$category->getId()][$prevId];
                }

                $allValues[$category->getId()][$dayId] = $value;

                if (!isset($totalValues['byAll'][$dayId])) {
                    $totalValues['byAll'][$dayId] = 0;
                }
                if ($categoryGetMethod == 'getRoomTypes') {
                    $hotelTotalValueTitle = 'total_' . $category->getHotel()->getId();
                    $addition = $this->getRequest()->query->get('type') === 'hotel_occupancy'
                        ? ($category->getRooms()->count() !== 0
                            ? ($value * $category->getRooms()->count()  / $numberOfRoomsByHotels[$category->getHotel()->getId()])
                            : 0)
                        : $value;
                    $totalValues[$hotelTotalValueTitle][$dayId] = isset($totalValues[$hotelTotalValueTitle][$dayId])
                        ? $totalValues[$hotelTotalValueTitle][$dayId] + $addition
                        : $addition;
                }
//$pers = $rooms / $total * 100; $rooms = $pers / 100 * $total
                $totalValues['byAll'][$dayId] = $totalValues['byAll'][$dayId] + $value;
                if ($this->isDisplayedCategory($category, $requestedRoomTypes, $categoryGetMethod)) {
                    if ($months) {
                        if ($date->format('j') == 15) {
                            $javascriptDate = '@Date.UTC(' . $date->format('Y') . ', ' . ($date->format('n') - 1) . ', 15)@';
                            $series[$i]['data'][] = [$javascriptDate, $value];
                        }
                    } else {
                        $javascriptDate = '@Date.UTC(' . $date->format('Y') . ', ' . ($date->format('n') - 1) . ', ' . $date->format('j') . ')@';
                        $series[$i]['data'][] = [$javascriptDate, $value];
                    }
                }
            }

            if (isset($series[$i])) {
                $i++;
            }
        }

        $totalByHotelsOptions = [];
        if ($categoryGetMethod == 'getRoomTypes') {
            foreach ($this->$categoryGetMethod() as $category) {
                $hotelTotalValueTitle = 'total_' . $category->getHotel()->getId();
                if (!in_array($hotelTotalValueTitle, $totalByHotelsOptions)
                    && (in_array($hotelTotalValueTitle, $requestedRoomTypes) || count($requestedRoomTypes) == 0)
                ) {
                    $totalByHotelsOptions[$hotelTotalValueTitle] = 'Итого ' . $category->getHotel()->getName();
                }
            }
        }

        if(count($series) <= 1 && count($totalByHotelsOptions) == 0) {
            return $series;
        }

        $totalOptions = $totalByHotelsOptions;
        if (!$withoutTotal) {
            $totalOptions['byAll'] = $this->get('translator')->trans('controller.analyticsController.series_total_name');
        }

        foreach ($totalOptions as $totalOption => $totalOptionTitle) {
            $series[$i]['name'] = $totalOptionTitle;
            foreach ($this->getInterval() as $date) {
                $value = 0;
                $dayId = $date->format('d.m.Y');

                if ($months && $date->format('j') == 15) {
                    $dayId = $date->format('m.Y');
                }

                if (isset($totalValues[$totalOption][$dayId])) {
                    $value = $totalValues[$totalOption][$dayId];
                }

                if ($months) {
                    if ($date->format('j') == 15) {
                        $javascriptDate = '@Date.UTC(' . $date->format('Y') . ', ' . ($date->format('n') - 1) . ', 15)@';
                        $series[$i]['data'][] = [$javascriptDate, $value];
                    }
                } else {
                    $javascriptDate = '@Date.UTC(' . $date->format('Y') . ', ' . ($date->format('n') - 1) . ', ' . $date->format('j') . ')@';
                    $series[$i]['data'][] = [$javascriptDate, $value];
                }
            }
            $i++;
        }

        return array_reverse($series);
    }

    private function isDisplayedCategory($category, $requestedRoomTypes, $categoryGetMethod)
    {
        return $categoryGetMethod !== 'getRoomTypes'
            || (is_array($requestedRoomTypes)
                && (count($requestedRoomTypes) == 0
                    || in_array($category->getId(), $requestedRoomTypes)
                    || in_array('allrooms_' . $category->getHotel()->getId(), $requestedRoomTypes)));
    }

    /**
     * @return Package[]|Cursor
     */
    private function getPackages()
    {
        $roomTypesIds = $this->getRoomTypes(true);

        $period = $this->getInterval();
        $begin = $period->getStartDate();
        $end = $period->getEndDate();
        $end->modify('+1 day');

        $qb = $this->dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');
        if (count($roomTypesIds)) {
            $qb->field('roomType.id')->in($roomTypesIds);
        }
        $qb->addOr($qb->expr()->field('createdAt')->range($begin, $end))
            ->sort('begin', 'asc');

        return $qb->getQuery()->execute();
    }

    /**
     * @param array $packages
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getPackagesCashDocumentsByOrdersIds(array $packages)
    {
        $ordersIds = array_map(function (Package $package) {
            return $package->getOrder()->getId();
        }, $packages);

        $cashDocuments = $this->dm
            ->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder()
            ->field('order.id')->in($ordersIds)
            ->getQuery()
            ->execute()
            ->toArray();

        return $this->helper->sortByValueByCallback($cashDocuments, function(CashDocument $cashDocument) {
            return $cashDocument->getOrder()->getId();
        }, true);
    }

    /**
     * @param array $packages
     * @return array
     */
    private function getPackageServicesByPackageIds(array $packages)
    {
        $packagesIds = $this->helper->toIds($packages);
        $packageServices = $this->dm
            ->getRepository('MBHPackageBundle:PackageService')
            ->createQueryBuilder()
            ->field('package.id')->in($packagesIds)
            ->getQuery()
            ->execute()
            ->toArray();

        return $this->helper->sortByValueByCallback($packageServices, function(PackageService $packageService) {
            return $packageService->getPackage()->getId();
        }, true);
    }
}
