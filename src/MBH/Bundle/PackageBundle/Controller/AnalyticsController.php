<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * @Route("/analytics")
 */
class AnalyticsController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Index template
     * @Route("/", name="analytics")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
                        ->createQueryBuilder('q')
                        ->sort('hotel.id', 'asc')
                        ->getQuery()
                        ->execute()
        ;

        return [
            'types' => $this->container->getParameter('mbh.analytics.types'),
            'roomTypes' => $roomTypes
        ];
    }

    /**
     * Index template
     * @Route("/choose", name="analytics_choose", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesServicesAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {

            foreach ($package->getServices() as $packageService) {
                $id  = $packageService->getService()->getId();
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

        $chart = $this->getChart('Количество услуг');
        $chart->series($this->getSeries($data, 'getServices'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_cash_documents", name="analytics_sales_cash_documents")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesCashDocumentsAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $id  = $package->getRoomType()->getId();

            foreach ($package->getCashDocuments() as $cashDocument) {
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

        $chart = $this->getChart('Выручка');
        $chart->series($this->getSeries($data));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/hotel_occupancy", name="analytics_hotel_occupancy")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function hotelOccupancyAction()
    {
        $data = $newData = $total = [];

        foreach ($this->getPackages() as $package) {
            $id  = $package->getRoomType()->getId();

            if(!isset($total[$id])) {
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
            if(!isset($total[$id])) {
                continue;
            }
            foreach ($values as $dataId => $value) {
                $newData[$id][$dataId] =  round($value/$total[$id] , 2)*100;
            }
        }

        $chart = $this->getChart('Процент');
        $chart->series($this->getSeries($newData, 'getRoomTypes', true));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_managers", name="analytics_sales_managers")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesManagersAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {

            $username  = $package->getCreatedBy();

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

        $chart = $this->getChart('Количество путевок');
        $chart->series($this->getSeries($data, 'getManagers'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_sources", name="analytics_sales_sources")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
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

                $id  = $source->getId();
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

        $chart = $this->getChart('Количество путевок');
        $chart->series($this->getSeries($data, 'getSources'));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_packages", name="analytics_sales_packages")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Analytics:response.html.twig")
     */
    public function salesPackagesAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $id  = $package->getRoomType()->getId();
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

        $chart = $this->getChart('Количество путевок');
        $chart->series($this->getSeries($data));

        return $this->getResponse($chart);
    }

    /**
     * @Route("/sales_cash", name="analytics_sales_cash", defaults={"_format"="json"})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function salesCashAction()
    {
        $data = [];

        foreach ($this->getPackages() as $package) {
            $id  = $package->getRoomType()->getId();
            $day = $package->getCreatedAt()->format('d.m.Y');
            $month = $package->getCreatedAt()->format('m.Y');

            if (!isset($data[$id][$day])) {
                $data[$id][$day] = 0;
            }
            if (!isset($data[$id][$month])) {
                $data[$id][$month] = 0;
            }
            $data[$id][$day] += $package->getPrice();
            $data[$id][$month]+= $package->getPrice();
        }

        $chart = $this->getChart('Выручка');
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
        if ($request->get('months')) {
            $chart->chart->type('column');
        }
        $chart->chart->renderTo('analytics_filter_content');
        $chart->title->text($this->container->getParameter('mbh.analytics.types')[$request->get('type')]);
        $chart->yAxis->title(['text'  => $y]);
        $chart->xAxis->title(['text'  => 'Даты продажи']);
        $chart->xAxis->type('datetime');
        $chart->xAxis->dateTimeLabelFormats(['month' => '%e. %b','year' => '%b']);
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
            'html' => $this->renderView('MBHPackageBundle:Analytics:response.html.twig', ['chart' => $chart])
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
            $end->setTime(0 ,0 ,0);
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
            ->execute()
        ;
    }

    private function getServices()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $dm->getRepository('MBHPriceBundle:Service')->createQueryBuilder('s')
            ->sort(['hotel.id' => 'asc', 'fullName' => 'desc'])
            ->getQuery()
            ->execute()
            ;
    }

    private function getManagers()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        return $dm->getRepository('MBHUserBundle:User')->createQueryBuilder('s')
            ->sort(['lastName' => 'desc', 'username' => 'desc'])
            ->getQuery()
            ->execute()
            ;
    }

    /**
     * @param boolean $array
     * @return array
     */
    private function getRoomTypes($array = false)
    {
        $request = $this->getRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $roomTypesIds = [];
        if(!empty($request->get('roomType')) && is_array($request->get('roomType'))) {
            foreach ($request->get('roomType') as $id) {
                if (mb_stripos($id, 'allrooms_') !== false) {
                    $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->find(str_replace('allrooms_', '', $id));

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

        if ($array) {
            return $roomTypesIds;
        }
        $qb = $dm->getRepository('MBHHotelBundle:RoomType')
                 ->createQueryBuilder('q')
                 ->sort(['hotel.id' => 'asc', 'fullName' => 'desc'])
        ;

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

        return (string) $category;
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
        $series = $all = $allValues = [];
        $i = 0;
        foreach ($this->$categoryGetMethod() as $category) {
            $series[$i]['name'] = $this->getCategoryName($category);
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

                if (isset($values[$category->getId()][$dayId]))  {
                    $value = $values[$category->getId()][$dayId];
                }

                if ($cumulative && isset($allValues[$category->getId()][$prevId])) {
                    $value += $allValues[$category->getId()][$prevId];
                }

                $allValues[$category->getId()][$dayId] = $value;

                if (!isset($all[$dayId]))  {
                    $all[$dayId] = 0;
                }
                $all[$dayId] = $all[$dayId] + $value;

                if ($months) {
                    if ($date->format('j') == 15) {
                        $javascriptDate = '@Date.UTC(' . $date->format('Y'). ', ' . ($date->format('n') - 1) . ', 15)@';
                        $series[$i]['data'][] = [$javascriptDate, $value];
                    }
                } else {
                    $javascriptDate = '@Date.UTC(' . $date->format('Y'). ', ' . ($date->format('n') - 1) . ', ' . $date->format('j'). ')@';
                    $series[$i]['data'][] = [$javascriptDate, $value];
                }
            }
            $i++;
        }

        if(count($series) <= 1) {
            return $series;
        }

        if ($withoutTotal) {
            return array_reverse($series);
        }

        $series[$i]['name'] = 'Итог';
        foreach ($this->getInterval() as $date) {
            $value = 0;
            $dayId = $date->format('d.m.Y');

            if ($months && $date->format('j') == 15) {
                $dayId = $date->format('m.Y');
            }

            if (isset($all[$dayId]))  {
                $value = $all[$dayId];
            }

            if ($months) {
                if ($date->format('j') == 15) {
                    $javascriptDate = '@Date.UTC(' . $date->format('Y'). ', ' . ($date->format('n') - 1) . ', 15)@';
                    $series[$i]['data'][] = [$javascriptDate, $value];
                }
            } else {
                $javascriptDate = '@Date.UTC(' . $date->format('Y'). ', ' . ($date->format('n') - 1) . ', ' . $date->format('j'). ')@';
                $series[$i]['data'][] = [$javascriptDate, $value];
            }
        }

        return array_reverse($series);
    }

    /**
     * @return array
     */
    private function getPackages()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $roomTypesIds = $this->getRoomTypes(true);

        $period = iterator_to_array($this->getInterval());
        $begin = reset($period);
        $end = end($period);
        $end->modify('+1 day');

        $qb = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');
        if (count($roomTypesIds)) {
            $qb->field('roomType.id')->in($roomTypesIds);
        }
        $qb->addOr($qb->expr()->field('createdAt')->range($begin, $end))
           ->sort('begin', 'asc')
        ;

        return $qb->getQuery()->execute();
    }
}
