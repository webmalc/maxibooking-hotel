<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FieldsName;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Form\AnalyticsForm;
use MBH\Bundle\OnlineBundle\Form\SettingsOnlineForm\FieldsNameType;
use MBH\Bundle\OnlineBundle\Form\SettingsOnlineForm\FormType;
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
        $docs = $this->dm->getRepository(FormConfig::class)->findAll();
        $siteConfig = $this->get('mbh.site_manager')->getSiteConfig();
        $hasEnabledMBSite = $siteConfig !== null && $siteConfig->getIsEnabled();

        return [
            'docs' => $docs,
            'hasEnabledMBSite' => $hasEnabledMBSite
        ];
    }

    /**
     * @Route("/new", name="online_form_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_FORM_EDIT')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $entity = new FormConfig();

        $form = $this->createForm(FormType::class, $entity, [
            'user' => $this->getUser()->getUserName()
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.formController.settings_saved_success');

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
     * @ParamConverter(class="MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig")
     * @param Request $request
     * @param FormConfig $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, FormConfig $entity)
    {
        $oldConfigWidth = $entity->getFrameWidth();
        $oldConfigHeight = $entity->getFrameHeight();
        $onFullWidth = $entity->isFullWidth();

        $form = $this->createForm(FormType::class, $entity, [
            'user' => $this->getUser()->getUserName()
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.formController.settings_saved_success');

            if ($entity->getFrameHeight() != $oldConfigHeight
                || $entity->getFrameWidth() !== $oldConfigWidth
                || $onFullWidth != $entity->isFullWidth()) {
                $this->addFlash('warning', 'controller.formController.frame_sizes_changed');
            }

            return $this->afterSaveRedirect('online_form', $entity->getId());
        }

        return [
            'config' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }

    /**
     * @Route("/{id}/fields_name", name="online_form_fields_name")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ONLINE_FORM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig")
     * @param Request $request
     * @param FormConfig $formConfig
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function fieldsNameAction(Request $request, FormConfig $formConfig)
    {
        $form = $this->createForm(FieldsNameType::class, $formConfig->getFieldsName(), [
//            'user' => $this->getUser()->getUserName()
        ]);

        $form->handleRequest($request);
//
        if ($form->isValid()) {
//
            /** @var FieldsName $fieldsName */
            $fieldsName = $form->getData();
            $formConfig->setFieldsName($fieldsName);

            $this->get('mbh.form_data_handler')
                ->saveTranslationsFromMultipleFieldsForm($form, $request, $fieldsName->getNameNotEmptyProperties());

            $this->dm->persist($formConfig);
            $this->dm->flush();
//
            $this->addFlash('success', 'controller.formController.settings_saved_success');

            return $this->afterSaveRedirect('online_form_fields_name', $formConfig->getId(), [], '');
        }

        return [
            'config' => $formConfig,
            'form' => $form->createView(),
            'logs' => $this->logs($formConfig->getFieldsName()),
        ];
    }

    /**
     * @Route("/{id}/form_code", name="form_code")
     * @Template()
     * @param FormConfig $config
     * @return array
     */
    public function formCodeAction(FormConfig $config)
    {
        return [
            'config' => $config
        ];
    }

    /**
     * @Template()
     * @Route("/{id}/analytics", name="form_analytics")
     * @param FormConfig $config
     * @param Request $request
     * @return array
     */
    public function analyticsAction(FormConfig $config, Request $request)
    {
        $form = $this->createForm(AnalyticsForm::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->flush();
            $this->addFlash('success', 'controller.formController.settings_saved_success');
        }

        return [
            'config' => $config,
            'form' => $form->createView(),
            'logs' => $this->logs($config),
        ];
    }

    /**
     * Delete room.
     *
     * @Route("/{id}/delete", name="online_form_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ONLINE_FORM_DELETE')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, FormConfig::class, 'online_form');
    }
}