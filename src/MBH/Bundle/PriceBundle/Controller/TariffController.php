<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffChildOptions;
use MBH\Bundle\PriceBundle\Form\TariffFilterType;
use MBH\Bundle\PriceBundle\Form\TariffInheritanceType;
use MBH\Bundle\PriceBundle\Form\TariffPromotionsType;
use MBH\Bundle\PriceBundle\Form\TariffServicesType;
use MBH\Bundle\PriceBundle\Form\TariffType;
use MBH\Bundle\PriceBundle\Lib\TariffFilter;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("management/tariff")
 */
class TariffController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Show list filter
     *
     * @Route("/", name="tariff", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TARIFF_VIEW')")
     * @Template()
     *
     * @param Request $request
     * @return array| \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $tableParams = ClientDataTableParams::createFromRequest($request);

        $filter = new TariffFilter();
        $filter->setSearch($tableParams->getSearch());
        $filter->setHotel($this->hotel);

        $form = $this->createForm(TariffFilterType::class, $filter);

        $formData = (array)$request->get('form');
        $formData['search'] = $tableParams->getSearch();

        if ($request->isXmlHttpRequest()) {
            $form->submit($formData);
            $entities = $this->dm->getRepository('MBHPriceBundle:Tariff')->getFiltered($filter);

            return $this->render('MBHPriceBundle:Tariff:index.json.twig', [
                'entities' => $entities,
                'draw' => $request->get('draw'),
                'total' => $entities->count()
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Extend tariff
     *
     * @Route("/{id}/inherit", name="tariff_extend")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     * @param Request $request
     * @param Tariff $parent
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function extendAction(Request $request, Tariff $parent)
    {
        $tariff = new Tariff();
        $tariff
            ->setFullTitle($parent->getFullTitle() . '-' . $this->container->get('translator')->trans('price.tariffcontroller.children_tariff'))
            ->setHotel($parent->getHotel())
            ->setParent($parent)
            ->setIsEnabled(false)
        ;
        $this->dm->persist($tariff);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', $this->container->get('translator')->trans('price.tariffcontroller.children_tariff_successfully_created'));

        return $this->redirect($this->generateUrl('tariff_edit', ['id' => $tariff->getId()]));
    }

    /**
     * Copy tariff
     *
     * @Route("/{id}/copy", name="tariff_copy")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function copyAction(Request $request, Tariff $parent)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($parent->getHotel()) || $parent->getParent()) {
            throw $this->createNotFoundException();
        }

        $new = clone $parent;
        $this->dm->persist($new);
        $this->dm->flush();

        //promotions
        foreach ($parent->getPromotions() as $promotion) {
            $new->addPromotion(
                $this->dm->getRepository('MBHPriceBundle:Promotion')->find($promotion->getId())
            );
        }
        //services
        foreach ($parent->getServices() as $service) {
            $new->addService(
                $this->dm->getRepository('MBHPriceBundle:Service')->find($service->getId())
            );
        }
        foreach ($parent->getDefaultServices() as $defaultService) {
            $new->addDefaultService($defaultService);
        }
        $this->dm->persist($new);
        $this->dm->flush();

        $query = ['isEnabled' => true, 'tariff.$id' => new \MongoId($parent->getId())];
        $update = ['tariff' => ['$ref' => 'Tariffs', '$id' => new \MongoId($new->getId())]];

        //Cache
        $this->get('mbh.mongo')->copy('PriceCache', $query, $update);
        $this->get('mbh.mongo')->copy('Restriction', $query, $update);
        $this->get('mbh.mongo')->copy('RoomCache', $query, $update);

        $this->addFlash('success', 'price.tariffcontroller.tariff_successfully_copied');

        return $this->redirect($this->generateUrl('tariff_edit', ['id' => $new->getId()]));
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tariff_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tariff();
        $form = $this->createForm(TariffType::class, $entity, [
            'hotel' => $this->hotel
        ]);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tariff_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_TARIFF_NEW')")
     * @Template("MBHPriceBundle:Tariff:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Tariff();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());

        $form = $this->createForm(TariffType::class, $entity, [
            'hotel' => $this->hotel
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'price.tariffcontroller.tariff_successfully_created');
            try {
                $this->invalidateCache($entity);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка при инвалидации кэша.');
            }

            return $this->afterSaveRedirect('tariff', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="tariff_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template("MBHPriceBundle:Tariff:edit.html.twig")
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function updateAction(Request $request, Tariff $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffType::class, $entity, [
            'hotel' => $this->hotel
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'price.tariffcontroller.entry_successfully_updated');

            try {
                $this->invalidateCache($entity);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка при инвалидации кэша.');
            }
            return $this->afterSaveRedirect('tariff', $entity->getId());
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
     * @Route("/{id}/edit", name="tariff_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editAction(Tariff $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffType::class, $entity, [
            'hotel' => $this->hotel
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/edit/{id}/promotions", name="tariff_promotions_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editPromotionsAction(Request $request, Tariff $tariff)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffPromotionsType::class, $tariff);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($tariff);
            $this->dm->flush();

            $this->addFlash('success','price.tariffcontroller.entry_successfully_updated');

            try {
                $this->invalidateCache($tariff);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка в инвалидации кэша.');
            }
            return $this->isSavedRequest() ?
                $this->redirectToRoute('tariff_promotions_edit', ['id' => $tariff->getId()]) :
                $this->redirectToRoute('tariff');
        }

        return [
            'entity' => $tariff,
            'form' => $form->createView(),
            'logs' => $this->logs($tariff)
        ];
    }

    /**
     * @Route("/edit/{id}/inheritance", name="tariff_inheritance_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     * @param  Request $request
     * @param Tariff $tariff
     * @return array
     */
    public function editInheritanceAction(Request $request, Tariff $tariff)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel()) || !$tariff->getParent()) {
            throw $this->createNotFoundException();
        }


        $options = $tariff->getChildOptions() ? $tariff->getChildOptions() : new TariffChildOptions() ;
        $form = $this->createForm(TariffInheritanceType::class, $options, ['parent' => $tariff->getParent()]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $tariff->setChildOptions($options);
            $this->dm->persist($tariff);
            $this->dm->flush();

            $this->addFlash('success','price.tariffcontroller.entry_successfully_updated');

            try {
                $this->invalidateCache($tariff);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()
                    ->set('error', 'Ошибка в инвалидации кэша.');
            }
            return $this->isSavedRequest() ?
                $this->redirectToRoute('tariff_inheritance_edit', ['id' => $tariff->getId()]) :
                $this->redirectToRoute('tariff');
        }

        return [
            'tariff' => $tariff,
            'form' => $form->createView(),
            'logs' => $this->logs($tariff),
        ];
    }

    /**
     * @Route("/edit/{id}/services", name="tariff_services_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editServicesAction(Request $request, Tariff $tariff)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(TariffServicesType::class, $tariff, [
            'services_all' => $this->dm->getRepository('MBHPriceBundle:Service')->getAvailableServicesForTariff($tariff, true),
            'services' => $this->dm->getRepository('MBHPriceBundle:Service')->getAvailableServicesForTariff($tariff)
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($tariff);
            $this->dm->flush();

            $this->addFlash('success', 'price.tariffcontroller.entry_successfully_updated');

            return $this->isSavedRequest() ?
                $this->redirectToRoute('tariff_services_edit', ['id' => $tariff->getId()]) :
                $this->redirectToRoute('tariff');
        }

        return [
            'tariff' => $tariff,
            'form' => $form->createView(),
            'logs' => $this->logs($tariff),
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tariff_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:Tariff', 'tariff');
    }

    /**
     * @param Tariff $tariff
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    private function invalidateCache(Tariff $tariff)
    {
        $this->get('mbh_search.invalidate_queue_creator')->addToQueue($tariff);
    }

}
