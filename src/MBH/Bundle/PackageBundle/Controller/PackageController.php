<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\PackageMainType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

class PackageController extends Controller implements CheckHotelControllerInterface
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
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="package_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
                new PackageMainType(), $entity, ['arrivals' => $this->container->getParameter('mbh.package.arrivals')]
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }
    
    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Package:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
                new PackageMainType(), $entity, ['arrivals' => $this->container->getParameter('mbh.package.arrivals')]
        );
        
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;

            return $this->afterSaveRedirect('package', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
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
        
        $this->get('mbh.room.cache.generator')->decrease(
            $package->getRoomType(), $package->getBegin(), $package->getEnd()
        );

        $request->getSession()
                ->getFlashBag()
                ->set('success', 'Бронь успешно создана.')
        ;
        
        return $this->redirect($this->generateUrl('package_edit', ['id' => $package->getId()]));
    }
    
    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $roomType = $entity->getRoomType();
        $begin = $entity->getBegin();
        $end = $entity->getEnd();
        
        $dm->remove($entity);
        $dm->flush($entity);

        $this->get('mbh.room.cache.generator')->increase($roomType, $begin, $end);
        
        $this->getRequest()
             ->getSession()
             ->getFlashBag()
             ->set('success', 'Запись успешно удалена.');

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }

}
