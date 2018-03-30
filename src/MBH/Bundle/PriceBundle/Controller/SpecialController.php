<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Form\BatchSpecialPromotionApplyType;
use MBH\Bundle\PriceBundle\Form\SpecialFilterType;
use MBH\Bundle\PriceBundle\Form\SpecialType;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherException;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherHolder;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("management/special")
 */
class SpecialController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Show list filter
     *
     * @Route("/", name="special", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_VIEW')")
     * @Template()
     *
     * @param Request $request
     * @return array| \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */

    public function indexAction(Request $request)
    {
        $filter = new SpecialFilter();
        $filter->setBegin(new \DateTime('midnight'));
        $filter->setHotel($this->hotel);

        $form = $this->createForm(SpecialFilterType::class, $filter);

        if ($request->isXmlHttpRequest()) {
            $form->submit($request->get('form'));
            $tableFilter = ClientDataTableParams::createFromRequest($request);
            $entities = $this->dm->getRepository('MBHPriceBundle:Special')->getTableFiltered($filter, $tableFilter);

            return $this->render(
                'MBHPriceBundle:Special:list.json.twig',
                [
                    'entities' => $entities->toArray(),
                    'draw' => $tableFilter->getDraw(),
                    'total' => $entities->count(),
                    'filtered' => $entities->count(),
                ]
            );
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new/{virtual}/{room}/{begin}/{end}", name="special_new", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_NEW')")
     * @Template()
     * @param Request $request
     * @param Room|null $virtual
     * @param RoomType|null $room
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(
        Request $request,
        Room $virtual = null,
        RoomType $room = null,
        \DateTime $begin = null,
        \DateTime $end = null
    ) {
        $entity = new Special();
        $entity->setHotel($this->hotel);
        if ($virtual) {
            $entity->setVirtualRoom($virtual);
        }
        if ($room) {
            $entity->addRoomType($room);
        }
        if ($begin) {
            $entity->setBegin($begin);
            $displayBegin = clone($begin);
            $entity->setDisplayFrom($displayBegin->modify('-7 days'));
        }
        if ($end) {
            $entity->setEnd($end);
            //Не ошибка, меняем на период от даты начала действия спец предложения
            $displayEnd = clone($begin);
            $entity->setDisplayTo($displayEnd->modify('+ 7 days'));
        }
        $entity->setLimit(1);
        $specialName = $this->createSpecialName($room, $begin, $end, $virtual);
        $entity->setFullTitle($specialName);

        /** @var Tariff $baseTariff */
        $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($this->hotel);
        $entity->addTariff($baseTariff);

        $form = $this->createForm(SpecialType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'document.saved');

            return $this->afterSaveRedirect('special', $entity->getId());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function createSpecialName(
        RoomType $roomType = null,
        \DateTime $begin = null,
        \DateTime $end = null,
        Room $virtualRoom = null
    ) {
        $roomTypeName = str_replace(' ', '_', ($roomType ? $roomType->getName() : ''));
        $beginAsString = $begin ? $begin->format('d_m-Y') : '';
        $endAsString = $end ? $end->format('d_m_Y') : '';
        $virtualName = $virtualRoom ? '_'.$virtualRoom->getName() : '';
        $specialName = "Спец_{$roomTypeName}_{$beginAsString}_{$endAsString}_{$virtualName}";

        return $specialName;
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="special_edit", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Special")
     *
     * @param Request $request
     * @param Special $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Special $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(SpecialType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();
            /** Висел слушатель но какие то глюки. Указываю тут явно пересчет. */
            $this->container->get('mbh.special_handler')->calculatePrices([$entity->getId()]);

            $this->addFlash('success', 'document.saved');

            return $this->afterSaveRedirect('special', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="special_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_DELETE')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:Special', 'special');
    }

    /**
     * @param Special $special
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @return array
     * @Route("/{id}/booking/{adults}/{children}/{infants}", name="special_booking", defaults={"adults":0, "children":0}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_NEW')")
     * @Template()
     */
    public function bookingAction(Special $special, int $adults = 0, int $children = 0, int $infants = 0)
    {
        $search = $this->get('mbh.online.data_provider_special');
        $searchData = $this->get('mbh.online.search_form_data');
        $searchData
            ->setSpecial($special)
            ->setRoomType($special->getVirtualRoom()->getRoomType())
            ->setForceCapacityRestriction(true);
        $specialResult = $search->getResults($searchData);
        $searchResult = null;
        $errors = [];
        if (count($specialResult)) {
            $searchResult = reset($specialResult)->getResults()->first() ?? null;
            /** @var SearchResult $searchResult */
            if (!$searchResult || $searchResult->getVirtualRoom() === null) {
                $errors[] = 'Возможно спецпредложение уже не активно.';
            }
        } else {
            $errors[] = 'Поиск не вернул результат, проверьте даты заезда-выезда, ограничения.';
        }
        if (!$special->getRemain()) {
            $errors[] = 'Спецпредложение вероятно уже выкуплено.';
        }

        return [
            'special' => $special,
            'searchResult' => $searchResult,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'errors' => $errors,
        ];
    }

    /**
     * @param Special $special
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/close", name="special_close", options={"expose"=true} )
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     */
    public function closeSpecialAction(Special $special)
    {
        $special->setIsEnabled(false);
        $this->dm->flush();

        return $this->redirectToRoute('special');
    }

    /**
     * @param Special $special
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/recalculate", name="special_recalculate", options={"expose"=true} )
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     */
    public function recalculateSpecialAction(Special $special = null)
    {
        if ($special) {
            $id = $special->getId();
            $this->get('mbh.special_handler')->calculatePrices([$id]);
        }

        return $this->redirectToRoute('special_edit', ['id' => $special->getId()]);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/batch/promotion/apply", name="special_batch_promotion_apply", options={"expose"=true} )
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     */
    public function batchSpecialPromotionApplyAction(Request $request)
    {
        $form = $this->createForm(
            BatchSpecialPromotionApplyType::class,
            null,
            [
                'action' => $this->generateUrl('special_batch_promotion_apply'),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SpecialBatcherHolder $holder */
            $holder = $form->getData();
            $batcher = $this->get('mbh.batcher');
            $specialHandler = $this->get('mbh.special_handler');
            try {
                $batcher->batchPromotionToSpecialApply($holder);
                $specialHandler->calculatePrices($holder->getSpecialIds());

                return new JsonResponse(['result' => 'ok'], 200);
            } catch (SpecialBatcherException $e) {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }

        }

        return $this->render(
            '@MBHPrice/Special/batch_promotion_apply.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

}
