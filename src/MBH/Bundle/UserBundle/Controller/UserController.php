<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType;
use MBH\Bundle\PackageBundle\Form\DocumentRelationType;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\UserSecurityType;
use MBH\Bundle\UserBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     * @Security("is_granted('ROLE_USER_VIEW_FA')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHUserBundle:User')->createQueryBuilder()
            ->sort('username', 'asc')
            ->getQuery()
            ->execute()
        ;

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();

        $allowNotificationTypes = $this->dm->getRepository('MBHBaseBundle:NotificationType')->getStuffType();

        $entity->setAllowNotificationTypes($allowNotificationTypes->toArray());
        $form = $this->createForm(UserType::class,
            $entity, ['roles' => $this->container->getParameter('security.role_hierarchy.roles')]
        );

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="user_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER_NEW')")
     * @Template("MBHUserBundle:User:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createForm(UserType::class,
            $entity, ['roles' => $this->container->getParameter('security.role_hierarchy.roles')]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.profileController.record_saved_success');

            return $this->afterSaveRedirect('user', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER_EDIT')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     * @param User $entity
     * @return array
     */
    public function editAction(User $entity)
    {
        $hasHotels = [];
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        foreach ($hotels as $hotel) {
//            $aclProvider = $this->get('security.acl.provider');
//            $objectIdentity = ObjectIdentity::fromDomainObject($hotel);
//            try {
//                $acl = $aclProvider->findAcl($objectIdentity);
//            } catch (AclNotFoundException $e) {
//                $acl = $aclProvider->createAcl($objectIdentity);
//            }
//
//            $securityIdentity = new UserSecurityIdentity($entity, \MBH\Bundle\UserBundle\Document\User::class);
//
//            try {
//                if ($acl->isGranted([MaskBuilder::MASK_MASTER], [$securityIdentity])) {
//                    $hasHotels[] = $hotel;
//                }
//            } catch (NoAceFoundException $e) {
//
//            }
            $hasHotels[] = $hotel;
        }

        $form = $this->createForm(UserType::class,
            $entity, [
                'roles'  => $this->container->getParameter('security.role_hierarchy.roles'),
                'isNew'  => false,
            ]
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }


    /**
     * @Route("/{id}/edit/document", name="user_document_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_USER_EDIT')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     * @param User $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editDocumentAction(User $entity, Request $request)
    {
        $entity->getDocumentRelation() ?: $entity->setDocumentRelation(new DocumentRelation());

        $form = $this->createForm(DocumentRelationType::class, $entity, [
            'data_class'  => \MBH\Bundle\UserBundle\Document\User::class,
            'citizenship' => false,
            'birthplace'  => false,
        ]);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $this->get('fos_user.user_manager')->updateUser($entity);
                $this->addFlash('success', 'controller.profileController.record_edited_success');

                return $this->afterSaveRedirect('user', $entity->getId(), [], '_document_edit');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/security", name="user_security_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_USER_EDIT')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     * @param User $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editSecurityAction(User $entity, Request $request)
    {
        $form = $this->createForm(UserSecurityType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($entity);
            $this->addFlash('success', 'controller.profileController.record_edited_success');

            return $this->afterSaveRedirect('user', $entity->getId(), [], '_security_edit');
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/address", name="user_address_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_USER_EDIT')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     * @param User $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAddressAction(User $entity, Request $request)
    {
        $entity->getAddressObjectDecomposed() ?: $entity->setAddressObjectDecomposed(new AddressObjectDecomposed());

        $form = $form = $this->createForm(AddressObjectDecomposedType::class, $entity->getAddressObjectDecomposed());

        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $this->get('fos_user.user_manager')->updateUser($entity);
                $this->addFlash('success', 'controller.profileController.record_edited_success');

                return $this->afterSaveRedirect('user', $entity->getId(), [], '_address_edit');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER_EDIT')")
     * @Template("MBHUserBundle:User:edit.html.twig")
     * @ParamConverter(name="entity", class="MBHUserBundle:User")
     * @param Request $request
     * @param User $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, User $entity)
    {
        $form = $this->createForm(
            UserType::class,
            $entity,
            [
                'roles' => $this->container->getParameter('security.role_hierarchy.roles'),
                'isNew' => false
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $newPassword = $form->get('newPassword')->getData();

            if ($newPassword != null) {
                $entity->setPlainPassword($newPassword);
            }

            if ($entity->getLocale()) {
                $this->get('session')->set('_locale', $entity->getLocale());
            }
            $this->container->get('fos_user.user_manager')->updateUser($entity);

            $this->addFlash('success', 'controller.profileController.record_edited_success');

            return $this->afterSaveRedirect('user', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
            'logs'   => $this->logs($entity),
        ];
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

            $securityIdentity = new UserSecurityIdentity($user, \MBH\Bundle\UserBundle\Document\User::class);

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
     * @Security("is_granted('ROLE_USER_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHUserBundle:User', 'user');
    }

}
