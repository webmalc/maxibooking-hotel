<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Form\FormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/form")
 */
class FormController extends Controller  implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="online_form")
     * @Method("GET")
     * @Security("is_granted('ROLE_ONLINE_FORM')")
     * @Template()
     */
    public function indexAction()
    {
        $docs = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findAll();

        return [
            'docs' => $docs
        ];
    }

    /**
     * @Route("/new", name="online_form_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_FORM_EDIT')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $this->setLocaleByRequest();

        $entity = new FormConfig();

        $form = $this->createForm(new FormType(), $entity, [
            'paymentTypes' => $this->container->getParameter('mbh.online.form')['payment_types']
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.formController.settings_saved_success'))
            ;

            return $this->afterSaveRedirect('online_form', $entity->getId());
        }

        return [
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.online.form')
        ];
    }

    /**
     * @Route("/{id}/edit", name="online_form_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_FORM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHOnlineBundle:FormConfig")
     */
    public function editAction(Request $request, FormConfig $entity)
    {
        $this->setLocaleByRequest();

        $form = $this->createForm(new FormType(), $entity, [
            'paymentTypes' => $this->container->getParameter('mbh.online.form')['payment_types']
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.formController.settings_saved_success'))
            ;

            return $this->afterSaveRedirect('online_form', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'config' => $this->container->getParameter('mbh.online.form')
        ];
    }

    /**
     * Delete room.
     *
     * @Route("/{id}/delete", name="online_form_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ONLINE_FORM_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHOnlineBundle:FormConfig', 'online_form');
    }
}