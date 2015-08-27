<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\UserType;
use MBH\Bundle\UserBundle\Form\UserSecurityType;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

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
        $entities = $this->dm->getRepository('MBHUserBundle:User')->createQueryBuilder('q')
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

        $form = $this->createForm(new UserType(true, $this->container->getParameter('security.role_hierarchy.roles')),
            $entity, ['admin' => $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')]
        );

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
        $form = $this->createForm(new UserType(true, $this->container->getParameter('security.role_hierarchy.roles')),
            $entity, ['admin' => $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')]
        );
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->updateAcl($entity, $form);

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.profileController.record_saved_success'));

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
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     */
    public function editAction(User $entity)
    {
        $hasHotels = [];
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        foreach ($hotels as $hotel) {
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($hotel);
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch (AclNotFoundException $e) {
                $acl = $aclProvider->createAcl($objectIdentity);
            }

            $securityIdentity = new UserSecurityIdentity($entity, 'MBH\Bundle\UserBundle\Document\User');

            try {
                if ($acl->isGranted([MaskBuilder::MASK_MASTER], [$securityIdentity])) {
                    $hasHotels[] = $hotel;
                }
            } catch (NoAceFoundException $e) {

            }
        }
        $form = $this->createForm(new UserType(false, $this->container->getParameter('security.role_hierarchy.roles')),
            $entity, ['admin' => $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'), 'hotels' => $hasHotels]
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }



    /**
     * @Route("/{id}/edit/document", name="user_document_edit")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     */
    public function editDocumentAction(User $entity, Request $request)
    {
        $entity->getDocumentRelation() ?: $entity->setDocumentRelation(new DocumentRelation());

        $form = $this->createForm('mbh_document_relation', $entity, [
            'data_class' => 'MBH\Bundle\UserBundle\Document\User',
            'citizenship' => false,
            'birthplace' => false
        ]);

        if($request->isMethod(Request::METHOD_PUT)) {
            $form->submit($request);
            if($form->isValid()) {
                $this->get('fos_user.user_manager')->updateUser($entity);

                return $this->redirectToRoute('user_document_edit', ['id' => $entity->getId()]);
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * @Route("/{id}/edit/security", name="user_security_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     */
    public function editSecurityAction(User $entity, Request $request)
    {
        $form = $this->createForm(new UserSecurityType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($entity);

            return $this->redirectToRoute('user_security_edit', ['id' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * @Route("/{id}/edit/address", name="user_address_edit")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     */
    public function editAddressAction(User $entity, Request $request)
    {
        $entity->getAddressObjectDecomposed() ?: $entity->setAddressObjectDecomposed(new AddressObjectDecomposed());

        $form = $form = $this->createForm('mbh_address_object_decomposed', $entity->getAddressObjectDecomposed());

        if($request->isMethod(Request::METHOD_PUT)) {
            $form->submit($request);
            if($form->isValid()) {
                $this->get('fos_user.user_manager')->updateUser($entity);

                return $this->redirectToRoute('user_address_edit', ['id' => $entity->getId()]);
            }
        }

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
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     */
    public function updateAction(Request $request, User $entity)
    {
        $form = $this->createForm(new UserType(false, $this->container->getParameter('security.role_hierarchy.roles')),
            $entity, ['admin' => $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')]
        );

        $form->submit($request);

        if ($form->isValid()) {

            $newPassword = $form->get('newPassword')->getData();

            if ($newPassword != null) {
                $entity->setPlainPassword($newPassword);
            }

            $this->container->get('fos_user.user_manager')->updateUser($entity);

            //update ACL
            $this->updateAcl($entity, $form);

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.profileController.record_edited_success'));

            return $this->afterSaveRedirect('user', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    private function updateAcl(User $user, $form)
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        foreach ($hotels as $hotel) {
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($hotel);
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch (AclNotFoundException $e) {
                $acl = $aclProvider->createAcl($objectIdentity);
            }

            $securityIdentity = new UserSecurityIdentity($user, 'MBH\Bundle\UserBundle\Document\User');

            foreach ($acl->getObjectAces() as $i => $ace) {
                if ($ace->getSecurityIdentity() == $securityIdentity) {
                    $acl->deleteObjectAce($i);
                }
            }

            if (!empty($form['hotels'])) {
                foreach ($form['hotels']->getData() as $formHotel) {
                    if ($formHotel->getId() == $hotel->getId()) {
                        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_MASTER);
                    }
                }
            }

            $aclProvider->updateAcl($acl);
        }

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
