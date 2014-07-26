<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;

class PackageController extends Controller
{

    /**
     * List entities
     *
     * @Route("/", name="package")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Create new entity
     *
     * @Route("/new", name="package_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$request->get('begin') ||
                !$request->get('end') ||
                !$request->get('adults') === null ||
                !$request->get('children') === null ||
                !$request->get('roomType') ||
                !$request->get('food')
        ) {
            return [];
        }

        //Set query
        $query = new SearchQuery();
        $query->begin = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('begin') . ' 00:00:00');
        $query->end = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('end') . ' 00:00:00');
        $query->adults = (int) $request->get('adults');
        $query->children = (int) $request->get('children');
        if (!empty($request->get('tariff'))) {
            $query->tariff = $request->get('tariff');
        }
        $query->addRoomType($request->get('roomType'));

        $results = $this->get('mbh.package.search')->search($query);

        if (count($results) != 1) {
            return [];
        }

        $package = new Package();
        $package->setBegin($results[0]->getBegin())
                ->setEnd($results[0]->getEnd())
                ->setAdults($results[0]->getAdults())
                ->setChildren($results[0]->getChildren())
                ->setTariff($results[0]->getTariff())
                ->setStatus('offline')
                ->setRoomType($results[0]->getRoomType())
                ->setFood($request->get('food'))
                ->setPaid(0)
                ->setPrice($results[0]->getPrice($package->getFood()))
        ;

        $errors = $this->get('validator')->validate($package);

        if (count($errors)) {
            return [];
        }

        $dm->persist($package);
        $dm->flush();
        
        $this->get('mbh.room.cache.generator')->generateForRoomTypeInBackground(
                $package->getRoomType(), $package->getBegin(), $package->getEnd()
        );

        return [];
    }

}
