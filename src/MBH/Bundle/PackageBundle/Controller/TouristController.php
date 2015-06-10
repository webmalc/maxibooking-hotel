<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Form\TouristExtendedType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\TouristType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/tourist")
 */
class TouristController extends Controller
{
    /**
     * Lists all entities.
     *
     * @Route("/", name="tourist")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }
    
    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="tourist_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')
                 ->createQueryBuilder('r')
                 ->skip($request->get('start'))
                 ->limit($request->get('length'))
                 ->sort('fullName', 'asc')
        ;

        $search = $request->get('search')['value'];
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('fullName')->equals(new \MongoRegex('/.*'. $search .'.*/ui')));
        }

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw')
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tourist_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tourist();
        $form = $this->createForm(
                new TouristType(), $entity, ['genders' => $this->container->getParameter('mbh.gender.types')]
        );

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tourist_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Tourist:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Tourist();
        $form = $this->createForm(
            new TouristType(), $entity, ['genders' => $this->container->getParameter('mbh.gender.types')]
        );
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success',
                    $this->get('translator')->trans('controller.touristController.record_created_success'));

            return $this->afterSaveRedirect('tourist', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * Edits an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Tourist:edit.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function updateAction(Request $request, Tourist $entity)
    {
        $form = $this->createForm(
            new TouristType(), $entity, ['genders' => $this->container->getParameter('mbh.gender.types')]
        );
        
        $form->submit($request);

        if ($form->isValid()) {
            /*var_dump($entity->getBirthplace());
            die();*/

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.touristController.record_edited_success'))
            ;
            
            return $this->afterSaveRedirect('tourist', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }
    
    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAction(Tourist $entity)
    {
        $form = $this->createForm(
            new TouristType(), $entity, ['genders' => $this->container->getParameter('mbh.gender.types')]
        );
        
        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/extended", name="tourist_edit_extended")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editExtendedAction(Tourist $entity, Request $request)
    {
        $form = $this->createForm(new TouristExtendedType(), $entity);

        if($request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                /*var_dump($entity->getBirthplace());
                die();*/

                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.touristController.record_edited_success'))
                ;

                return $this->afterSaveRedirect('tourist', $entity->getId());
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }
    
    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPackageBundle:Tourist', 'tourist');
    }
}
