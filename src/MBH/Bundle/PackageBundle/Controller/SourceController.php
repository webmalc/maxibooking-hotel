<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Form\PackageSourceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("management/source")
 */
class SourceController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Source action
     *
     * @Route("/", name="package_source")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $entity = new PackageSource();
        $form = $this->createForm(PackageSourceType::class, $entity, []);

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entities = $dm->getRepository('MBHPackageBundle:PackageSource')->createQueryBuilder('s')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute()
        ;

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.sourceController.record_created_success'))
            ;

            return $this->redirect($this->generateUrl('package_source'));


        }
        return [
            'form' => $form->createView(),
            'entities' => $entities,
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="package_source_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:PackageSource')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(PackageSourceType::class, $entity, []);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_source_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:Source:edit.html.twig")
     *
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:PackageSource')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(PackageSourceType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();



            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.sourceController.record_edited_success'))
            ;
            return $this->afterSaveRedirect('package_source', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_source_delete")
     * @Method("GET")
     * @ParamConverter(class="MBHPackageBundle:PackageSource")
     * @param PackageSource $entity
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(PackageSource $entity)
    {
        if (!$entity->getSystem()) {
            return $this->deleteEntity($entity->getId(), 'MBHPackageBundle:PackageSource', 'package_source');
        }

    }

}
