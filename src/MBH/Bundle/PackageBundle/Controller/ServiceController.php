<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\PackageBundle\Document\Package;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ServiceController
 * @package MBH\Bundle\PackageBundle\Controller
 *

 */
class ServiceController extends BaseController
{
    /**
     * @Route("/service/index", name="service_list")
     * @Method("GET")
     * @Security("is_granted('ROLE_SERVICES_REPORT')")
     * @Template()
     */
    public function indexAction()
    {
        $services = $categories = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']) as $hotel) {

            $serviceCats = $this->dm
                ->getRepository('MBHPriceBundle:ServiceCategory')
                ->findBy(['hotel.id' => $hotel->getId(), 'isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']);

            foreach ($serviceCats as $category) {
                $categories[(string)$hotel][$category->getId()] = (string)$category;

                $serviceDocs = $serviceCats = $this->dm
                    ->getRepository('MBHPriceBundle:Service')
                    ->findBy(['category.id' => $category->getId(), 'isEnabled' => true], ['fullTitle' => 'asc', 'title' => 'asc']);

                foreach ($serviceDocs as $serviceDoc) {
                    $services[$serviceDoc->getId()] = $serviceDoc;
                }
            }
        }

        return [
            'services' => $services,
            'categories' => $categories
        ];
    }

    /**
     * @Route("/service/resetValue/{id}", name="reset_total_overwrite_value")
     * @Security("is_granted('ROLE_ORDER_EDIT')")
     * @param Package $package
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resetTotalOverwriteValue(Package $package)
    {
        $package->setTotalOverwrite(0);
        $package->getOrder()->setTotalOverwrite(0);
        $this->dm->flush();
        return $this->redirectToRoute('package_edit', ['id' => $package->getId()]);
    }

    /**
     * @Route("/service/ajax", name="ajax_service_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_SERVICES_REPORT')")
     * @Template()
     */
    public function ajaxListAction(Request $request)
    {
        $begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $end = $this->get('mbh.helper')->getDateFromString($request->get('end'));
        $service = $request->get('service');
        $category = $request->get('category');
        $services = null;

        //cat services
        if (!empty($category) && empty($service)) {
            $serviceDocs = $serviceCats = $this->dm
                ->getRepository('MBHPriceBundle:Service')
                ->findBy(['category.id' => $category, 'isEnabled' => true]);

            $services = $this->get('mbh.helper')->toIds($serviceDocs);

        } else if (!empty($service)) {
            $services = [$service];
        }

        if (!$begin) {
            $begin = new \DateTime('midnight -7 days');
        }

        if (!$end) {
            $end = new \DateTime('midnight +1 day');
        }

        /** @var DocumentRepository $repository */
        $repository = $this->dm->getRepository('MBHPackageBundle:PackageService');

        $queryBuilder = $repository->createQueryBuilder();
        $queryBuilder->addNor($queryBuilder->expr()
            ->addOr($queryBuilder->expr()
                ->field('begin')->gt($begin)->addAnd($queryBuilder->expr()->field('begin')->gt($end))
            )->addOr($queryBuilder->expr()
                ->field('end')->lt($begin)->addAnd($queryBuilder->expr()->field('end')->lt($end))
            )
        );

        $tableParams = ClientDataTableParams::createFromRequest($request);
        $tableParams->setSortColumnFields([
            //1 => 'package.id',
            2 => 'begin',
            4 => 'nights',
            5 => 'persons',
            6 => 'amount',
            8 => 'total',
        ]);

        if ($firstSort = $tableParams->getFirstSort()) {
            $queryBuilder->sort($firstSort[0], $firstSort[1]);
        }

        if ($services !== null && is_array($services)) {
            $queryBuilder->field('service.id')->in($services);
        }

        if ($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        $count = $queryBuilder->getQuery()->count();

        $queryBuilder->skip($tableParams->getStart())->limit($tableParams->getLength());

        /** @var \MBH\Bundle\PackageBundle\Document\PackageService[] $results */
        $results = $queryBuilder->getQuery()->execute()->toArray();

        $totals = [
            'nights' => 0,
            'guests' => 0,
            'amount' => 0,
            'result' => 0,
            'payment' => 0,
            'dept' => 0,
        ];

        foreach ($results as $service) {
            if ($service->getCalcType() == 'per_night')
                $totals['nights'] += intval($service->getNights());
            if ($service->getCalcType() != 'not_applicable') {
                $totals['guests'] += intval($service->getPersons());
            }
            $totals['amount'] += intval($service->getAmount());
            $totals['result'] += $service->getTotal();
        }

        $totals['result'] = number_format($totals['result'], 2);
        $totals = json_encode($totals);

        if ($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        return [
            'results' => $results,
            'recordsFiltered' => $count,
            'totals' => $totals,
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }
}