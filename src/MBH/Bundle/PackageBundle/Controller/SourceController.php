<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
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
class SourceController extends BaseController implements CheckHotelControllerInterface
{
    /**
     * Source action
     *
     * @Route("/", name="package_source")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SOURCE_VIEW')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $entity = new PackageSource();
        $form = $this->createForm(PackageSourceType::class, $entity, []);

        $entities = $this->dm
            ->getRepository('MBHPackageBundle:PackageSource')
            ->createQueryBuilder()
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        if($request->isMethod('POST') && $this->isGranted('ROLE_SOURCE_NEW')) {
            $form->handleRequest($request);
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.sourceController.record_created_success');

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
     * @Security("is_granted('ROLE_SOURCE_EDIT')")
     * @Template()
     * @param PackageSource $packageSource
     * @return array
     */
    public function editAction(PackageSource $packageSource)
    {
        $form = $this->createForm(PackageSourceType::class, $packageSource, []);

        return [
            'entity' => $packageSource,
            'form' => $form->createView(),
            'logs' => $this->logs($packageSource)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_source_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_SOURCE_EDIT')")
     * @Template("MBHPackageBundle:Source:edit.html.twig")
     * @param Request $request
     * @param PackageSource $source
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, PackageSource $source)
    {
        $form = $this->createForm(PackageSourceType::class, $source);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($source);
            $this->dm->flush();

            $this->addFlash('success', 'controller.sourceController.record_edited_success');

            return $this->afterSaveRedirect('package_source', $source->getId());
        }

        return [
            'entity' => $source,
            'form' => $form->createView(),
            'logs' => $this->logs($source)
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_source_delete")
     * @Method("GET")
     * @ParamConverter(class="MBHPackageBundle:PackageSource")
     * @param PackageSource $entity
     * @Security("is_granted('ROLE_SOURCE_DELETE')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(PackageSource $entity)
    {
        if (!$entity->getSystem()) {
            return $this->deleteEntity($entity->getId(), 'MBHPackageBundle:PackageSource', 'package_source');
        }

        throw new \InvalidArgumentException('System package source can not be deleted!');
    }

}
