<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffChildOptions;
use MBH\Bundle\PriceBundle\Form\TariffInheritanceType;
use MBH\Bundle\PriceBundle\Form\TariffPromotionsType;
use MBH\Bundle\PriceBundle\Form\TariffServicesType;
use MBH\Bundle\PriceBundle\Form\TariffServiceType;
use MBH\Bundle\PriceBundle\Form\TariffType;
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
     * Lists all entities.
     *
     * @Route("/", name="tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities
        ];
    }

    /**
     * Extend tariff
     *
     * @Route("/{id}/inherit", name="tariff_extend")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     * @param Request $request
     * @param Tariff $parent
     * @return array
     */
    public function extendAction(Request $request, Tariff $parent)
    {
        $tariff = new Tariff();
        $tariff
            ->setFullTitle($parent->getFullTitle() . '-дочерний тариф')
            ->setHotel($parent->getHotel())
            ->setParent($parent)
            ->setIsEnabled(false)
        ;
        $this->dm->persist($tariff);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', 'Дочерний тариф успешно создан.');

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

        $request->getSession()->getFlashBag()
            ->set('success', 'Тариф успешно скопирован.');

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
        $form = $this->createForm(TariffType::class, $entity);

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

        $form = $this->createForm(TariffType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Тариф успешно создан. Теперь необходимо заполнить цены.');
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

        $form = $this->createForm(TariffType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

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

        $form = $this->createForm(TariffType::class, $entity);

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

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

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

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

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

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

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

}
