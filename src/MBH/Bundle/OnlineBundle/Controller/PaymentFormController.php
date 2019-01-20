<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;


use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\PaymentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment_form")
 */
class PaymentFormController extends Controller
{
    /**
     * @Route("/", name="online_payment_form")
     * @Method("GET")
     * @Security("is_granted('ROLE_ONLINE_PAYMENT_FORM')")
     * @Template()
     */
    public function indexAction()
    {
        $paymentForms = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')->findAll();

        $siteConfig = $this->get('mbh.site_manager')->getSiteConfig();
        $hasEnabledMBSite = $siteConfig !== null && $siteConfig->getIsEnabled();

        return [
            'paymentForms'     => $paymentForms,
            'hasEnabledMBSite' => $hasEnabledMBSite,
        ];
    }

    /**
     * @Route("/new", name="online_payment_form_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_PAYMENT_FORM_EDIT')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $entity = new PaymentFormConfig();

        $form = $this->createForm(PaymentFormType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.formController.settings_saved_success');

            return $this->afterSaveRedirect('online_payment_form', $entity->getId());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/edit", name="online_payment_form_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_PAYMENT_FORM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHOnlineBundle:PaymentFormConfig")
     * @param Request $request
     * @param PaymentFormConfig $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, PaymentFormConfig $entity)
    {
        $form = $this->createForm(PaymentFormType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.formController.settings_saved_success');

            return $this->afterSaveRedirect('online_payment_form', $entity->getId());
        }

        return [
            'config' => $entity,
            'form'   => $form->createView(),
            'logs'   => $this->logs($entity),
        ];
    }

    /**
     * @Route("/{id}/form_code", name="payment_form_code")
     * @Template()
     * @param PaymentFormConfig $config
     * @return array
     */
    public function formCodeAction(PaymentFormConfig $config)
    {
        return [
            'config'    => $config,
            'wrapperId' => PaymentFormConfig::WRAPPER_ID,
        ];
    }

    /**
     * Delete room.
     *
     * @Route("/{id}/delete", name="online_payment_form_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ONLINE_PAYMENT_FORM_DELETE')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHOnlineBundle:PaymentFormConfig', 'online_payment_form');
    }
}