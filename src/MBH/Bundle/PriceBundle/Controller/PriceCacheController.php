<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Form\PriceCacheGeneratorType;
use MBH\Bundle\PriceBundle\Lib\PriceCacheFactory;
use MBH\Bundle\PriceBundle\Lib\PriceCacheHolderDataGeneratorForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @Route("price_cache")
 */
class PriceCacheController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @var RoomTypeManager
     */
    private $manager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->manager = $this->get('mbh.hotel.room_type_manager');
    }

    /**
     * @Route("/", name="price_cache_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_VIEW')")
     * @Template()
     * @throws \Exception
     */
    public function indexAction()
    {
        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $getRoomTypeCallback = function () {
            return $this->manager->getRooms($this->hotel);
        };
        $roomTypes = $this->get('mbh.helper')->getFilteredResult($this->dm, $getRoomTypeCallback, $isDisableableOn);
        if ($this->clientConfig->isMBSiteEnabled()) {
            $emptyPeriodWarnings = $this->get('mbh.warnings_compiler')->getEmptyCacheWarningsAsStrings($this->hotel, 'price');
        }

        if (!empty($emptyPeriodWarnings)) {
            $this->addFlash('warning', join('<br>', $emptyPeriodWarnings));
        }

        return [
            'roomTypes' => $roomTypes,
            'tariffs' => $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'prices'),
            'displayDisabledRoomType' => !$isDisableableOn
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="price_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_VIEW')")
     * @Template()
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function tableAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if (!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if (!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+45 days');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $this->hotel
        ];

        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $inputRoomTypeIds = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));
        $roomTypesCallback = function () use ($inputRoomTypeIds) {
            return $this->manager->getRooms($this->hotel, $inputRoomTypeIds);
        };

        $roomTypes = $helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);
        if (empty($roomTypeIds = $inputRoomTypeIds)) {
            $roomTypeIds = $helper->toIds($roomTypes);
        }

        $cancelDate = null;
        if (!empty($displayedPricesDateString = $request->get('displayed-prices-date'))) {
            if (empty($displayedPricesTimeString = $request->get('displayed-prices-time'))) {
                $displayedPricesTimeString = '00:00';
            }
            $cancelDate = \DateTime::createFromFormat('d.m.Y H:i', $displayedPricesDateString . ' ' . $displayedPricesTimeString);
        }

        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetchWithCancelDate(
            $begin,
            $end,
            $this->hotel,
            $roomTypeIds,
            $request->get('tariffs') ? $request->get('tariffs') : [],
            $this->manager->useCategories,
            $cancelDate
        );

        if (!count($roomTypes)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.roomcachecontroller.room_type_is_not_found')]);
        }

        //get tariffs
        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->fetchChildTariffs($this->hotel, 'prices', $request->get('tariffs'));
        if (!count($tariffs)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.roomcachecontroller.tariffs_is_not_found')]);
        }

        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'priceCaches' => $priceCaches
        ]);
    }

    /**
     * @Route("/save", name="price_cache_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template("MBHPriceBundle:PriceCache:index.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $dates = [];

        $this->handlerNewPriceCache($request, $dates);
        $this->handlerUpdatePriceCache($request, $dates);

        if (!empty($dates)) {
            list($minDate, $maxDate) = $this->helper->getMinAndMaxDates($dates);
            $this->get('mbh.channelmanager')->updatePricesInBackground($minDate, $maxDate);
        }

        $this->get('mbh.cache')->clear('price_cache');

        return $this->redirect($this->generateUrl('price_cache_overview', [
            'begin'     => $request->get('begin'),
            'end'       => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
            'tariffs'   => $request->get('tariffs'),
        ]));
    }

    /**
     * @param Request $request
     * @param array $dates
     */
    private function handlerUpdatePriceCache(Request $request, array &$dates): void
    {
        $updateData = $request->get('updatePriceCaches') ?? [];

        if ($updateData === []) {
            return;
        }

        $holderErrorsAtUpdate = [];

        $validator = $this->get('validator');

        //update
        foreach ($updateData as $priceCacheId => $prices) {
            $priceCacheCallback = function () use ($priceCacheId) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->find($priceCacheId);
            };
            /** @var PriceCache $priceCache */
            $priceCache = $this->helper->getFilteredResult($this->dm, $priceCacheCallback);
            if (!$priceCache || $priceCache->getHotel() != $this->hotel) {
                continue;
            }

            //delete
            if (isset($prices['price']) && $prices['price'] === '') {
                $priceCache->setCancelDate(new \DateTime(), true);
                continue;
            }

            $factory = new PriceCacheFactory();
            $newPriceCache = $factory->create($prices);
            $newPriceCache
                ->setHotel($this->hotel)
                ->setCategoryOrRoomType($priceCache->getCategoryOrRoomType())
                ->setTariff($priceCache->getTariff())
                ->setDate($priceCache->getDate());

            $errorsArUpdate = $validator->validate($newPriceCache);
            $withoutErrorsAtUpdate = $errorsArUpdate->count() === 0;

            if ($withoutErrorsAtUpdate && !$priceCache->isSamePriceCaches($newPriceCache)) {
                $this->dm->persist($newPriceCache);
                $priceCache->setCancelDate(new \DateTime(), true);
            } else {
                $this->container->get('logger')->error('Error at update price cache.', iterator_to_array($errorsArUpdate));
                $holderErrorsAtUpdate[] = $errorsArUpdate;
            }

            $dates[] = $newPriceCache->getDate();
        }

        $this->addFlashBag($request, isset($withoutErrorsAtUpdate), $holderErrorsAtUpdate, 'обновлении');

        $this->dm->flush();
    }

    /**
     * @param Request $request
     * @param array $dates
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function handlerNewPriceCache(Request $request, array &$dates): void
    {
        $newData = $request->get('newPriceCaches') ?? [];

        if ($newData === []) {
            return;
        }

        $holderErrorsAtCreate = [];
        $validator = $this->get('validator');

        $availableTariffs = $this->helper->toIds(
            $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms')
        );

        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {
            $roomType = $this->manager->findRoom($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $this->hotel) {
                continue;
            }
            foreach ($roomTypeArray as $tariffId => $tariffArray) {
                $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
                if (!$tariff || $tariff->getHotel() != $this->hotel || !in_array($tariffId, $availableTariffs)) {
                    continue;
                }
                foreach ($tariffArray as $date => $prices) {
                    if (!isset($prices['price']) || $prices['price'] === '') {
                        continue;
                    }

                    $factory = new PriceCacheFactory();
                    $newPriceCache = $factory->create($prices);
                    $newPriceCache
                        ->setHotel($this->hotel)
                        ->setCategoryOrRoomType($roomType, $this->manager->useCategories)
                        ->setTariff($tariff)
                        ->setDate($this->helper->getDateFromString($date));

                    $errorsAtCreate = $validator->validate($newPriceCache);
                    $withoutErrorsAtCreate = $errorsAtCreate->count() === 0;

                    if ($withoutErrorsAtCreate) {
                        $this->dm->persist($newPriceCache);
                    } else {
                        $this->container->get('logger')->error('Error at create price cache.', iterator_to_array($errorsAtCreate));
                        $holderErrorsAtCreate[] = $errorsAtCreate;
                    }

                    $dates[] = $newPriceCache->getDate();
                }
            }
        }

        $this->addFlashBag($request, isset($withoutErrorsAtCreate), $holderErrorsAtCreate, 'создании');

        $this->dm->flush();
    }

    /**
     * @param Request $request
     * @param bool $isUse
     * @param array $holderErrors
     * @param string $action
     */
    private function addFlashBag(Request $request, bool $isUse, array $holderErrors, string $action): void
    {
        if ($isUse) {
            $successMessage = 'Изменения при %s записи успешно сохранены.';
            $errorMessage = 'Не удалось сохранить изменения при %s записей за %s. Попробуйте ещё раз или обратитесь к администратору.';
            if ($holderErrors === []) {
                $request->getSession()->getFlashBag()->add('success', sprintf($successMessage, $action));
            } else {
                $errDate = [];
                /** @var ConstraintViolationList $violations */
                foreach ($holderErrors as $violations) {
                    foreach ($violations as $violation) {
                        /** @var PriceCache $priceCacheErr */
                        $priceCacheErr = $violation->getRoot();
                        $errDate[] = $priceCacheErr->getDate()->format('d.m.Y');
                    }
                }

                $request->getSession()->getFlashBag()->add('warning', sprintf($errorMessage, $action , implode(', ', $errDate)));
            }
        }
    }

    /**
     * @Route("/generator", name="price_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template()
     */
    public function generatorAction(Request $request)
    {
        $generator = new PriceCacheHolderDataGeneratorForm();
        if ($request->getSession()->has('priceCacheGeneratorForm')) {
            $str = $request->getSession()->get('priceCacheGeneratorForm');

            if (is_string($str)) {
                /** @var PriceCacheHolderDataGeneratorForm $generator */
                $generator = unserialize($str);
                $generator->afterUnserialize($this->dm, $this->manager->useCategories);
            } else {
                $request->getSession()->remove('priceCacheGeneratorForm');
            }
        }
        $generator->setHotel($this->hotel);

        $form = $this->createForm(PriceCacheGeneratorType::class, $generator, [
            'useCategories' => $this->manager->useCategories,
        ]);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/generator/save", name="price_cache_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template("MBHPriceBundle:PriceCache:generator.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generatorSaveAction(Request $request)
    {
        $generator = new PriceCacheHolderDataGeneratorForm();
        $generator->setHotel($this->hotel);

        $form = $this->createForm(PriceCacheGeneratorType::class, $generator, [
            'useCategories' => $this->manager->useCategories
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var PriceCacheHolderDataGeneratorForm $generator */
            $generator = $form->getViewData();

            $session = $request->getSession();

            if ($generator->isSaveForm()) {
                $session->set('priceCacheGeneratorForm', serialize($generator));
            } else {
                $session->remove('priceCacheGeneratorForm');
            }

            $resultUpdate = $this->get('mbh.price.cache')->update($generator);

            $this->get('mbh.channelmanager')->updatePricesInBackground($generator->getBegin(), $generator->getEnd());
            $this->get('mbh.cache')->clear('price_cache');

            $session->getFlashBag()->set('success', $this->container->get('translator')->trans('price.roomcachecontroller.data_successfully_generate'));

            return $this->isSavedRequest() ?
                $this->redirectToRoute('price_cache_generator') :
                $this->redirectToRoute('price_cache_overview');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
