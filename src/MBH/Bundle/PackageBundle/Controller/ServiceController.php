<?php

namespace MBH\Bundle\PackageBundle\Controller;


use Doctrine\MongoDB\Query\Expr;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ServiceController
 * @package MBH\Bundle\PackageBundle\Controller
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ServiceController extends BaseController
{
    /**
     * @Route("/service/index", name="service_list")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        $services = [];

        foreach($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
            $categories = $hotel->getServicesCategories();
            foreach($categories as $category) {
                $group = $hotel->getName().':'.mb_strtolower($category->getName());
                $services[$group][$category->getId()] = 'Все услеги';
                foreach ($category->getServices() as $service) {
                    $services[$group][$service->getId()] = $service->getName();
                }
            }
        }

        return [
            'services' => $services
        ];
    }

    /**
     * @Route("/service/ajax", name="ajax_service_list", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function ajaxListAction(Request $request)
    {
        $begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $end = $this->get('mbh.helper')->getDateFromString($request->get('end'));
        $services = $request->get('services');

        if (!$begin) {
            $begin = new \DateTime('midnight -7 days');
        }

        if (!$end) {
            $end = new \DateTime('midnight +1 day');
        }

        $repository = $this->dm->getRepository('MBHPackageBundle:PackageService');

        $qb = $repository->createQueryBuilder();
        $qb->addNor($qb->expr()
            ->addOr($qb->expr()
                ->field('begin')->gt($begin)->addAnd($qb->expr()->field('begin')->gt($end))
            )->addOr($qb->expr()
                ->field('end')->lt($begin)->addAnd($qb->expr()->field('end')->lt($end))
            )
        );

        $tableParams = ClientDataTableParams::createFromRequest($request);
        $tableParams->setSortColumnFields([
            1 => 'package.id',
            2 => 'begin',
            4 => 'price',
            7 => 'createdAt',
        ]);

        $qb->skip($tableParams->getStart())->limit($tableParams->getLength());

        if ($firstSort = $tableParams->getFirstSort()) {
            $qb->sort($firstSort[0], $firstSort[1]);
        }

        if ($services) {
            /** @var ServiceCategory[] $categories */
            $categories = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->createQueryBuilder()->field('id')->in($services)->getQuery()->execute();
            foreach ($categories as $category) {
                foreach ($category->getServices() as $service) {
                    $services[] = $service->getId();
                }
            }

            $qb->field('service.id')->in($services);
        }

        if ($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        /** @var \MBH\Bundle\PackageBundle\Document\PackageService[] $results */
        $results = $qb->getQuery()->execute()->toArray();

        $total = $qb->group([], ['total' => 0, 'persons' => 0])->reduce('function(obj, prev){
            prev.persons += obj.persons;
            prev.total += obj.persons * obj.price;
        }')->getQuery()->getSingleResult();

        if(!$total)
            $total = ['persons' => 0, 'total' => 0];

        if($request->get('deleted') == 'on') {
            $this->dm->getFilterCollection()->enable('softdeleteable') ;
        }

        return [
            'results' => $results,
            'recordsFiltered' => count($results),
            'total' => $total,
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }
}