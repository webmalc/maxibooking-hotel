<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\UserType;

/**
 * User controller.
 * @Route("management/user")
 */
class UserController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $entities = $dm->getRepository('MBHUserBundle:User')->createQueryBuilder('q')
                       ->sort('username', 'asc')
                       ->getQuery()
                       ->execute()
        ;

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        
        $form = $this->createForm(new UserType(true, $this->container->getParameter('security.role_hierarchy.roles')), $entity);

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * Creates a new entity.
     *
     * @Route("/create", name="user_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHUserBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new User(array());
        $form = $this->createForm(new UserType(true, $this->container->getParameter('security.role_hierarchy.roles')), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;

            return $this->afterSaveRedirect('user', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new UserType(false, $this->container->getParameter('security.role_hierarchy.roles')), $entity);
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHUserBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new UserType(false, $this->container->getParameter('security.role_hierarchy.roles')), $entity);
        
        $form->bind($request);

        if ($form->isValid()) {

            $newPassword = $form->get('newPassword')->getData();
            
            if($newPassword != null) {
                $entity->setPlainPassword($newPassword);
            }
            
            $this->container->get('fos_user.user_manager')->updateUser($entity);
            
            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;

            return $this->afterSaveRedirect('user', $entity->getId());
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
     * @Route("/{id}/delete", name="user_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHUserBundle:User', 'user');
    }

}
