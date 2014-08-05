<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
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
     * Index template
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
     * Index template
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
        $chart->chart->renderTo('analytics-filter-content');
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
     * @param array $values
     * @return array
     */
    private function getSeries($values = array())
    {
        $request = $this->getRequest();
        $cumulative = $request->get('cumulative');
        $months = $request->get('months');
        $series = $all = $allValues = [];
        $i = 0;
        foreach ($this->getRoomTypes() as $roomType) {
            $series[$i]['name'] = $roomType->getHotel() . ': ' . $roomType;
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

                if (isset($values[$roomType->getId()][$dayId]))  {
                    $value = $values[$roomType->getId()][$dayId];
                }

                if ($cumulative && isset($allValues[$roomType->getId()][$prevId])) {
                    $value += $allValues[$roomType->getId()][$prevId];
                }

                $allValues[$roomType->getId()][$dayId] = $value;

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
