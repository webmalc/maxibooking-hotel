<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Form\PriceCacheGeneratorType;
use MBH\Bundle\PriceBundle\Lib\PriceCacheFactory;
use MBH\Bundle\PriceBundle\Lib\PriceCacheHolderDataGeneratorForm;
use MBH\Bundle\PriceBundle\Lib\PriceCacheSkippingDate;
use MBH\Bundle\PriceBundle\Services\PriceCacheResultUpdate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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

        $resultUpdate = $this->container->get('mbh.price.cache.result_update');

        $this->handlerNewPriceCache($request, $resultUpdate, $dates);
        $this->handlerUpdatePriceCache($request, $resultUpdate, $dates);

        if (!empty($dates)) {
            list($minDate, $maxDate) = $this->helper->getMinAndMaxDates($dates);
            try{
                $this->container->get('mbh.channelmanager.logger')
                    ->addInfo(
                        'Start to updatePrices for dates: ' .
                        $minDate->format('Y-m-d') . ' - ' . $maxDate->format('Y-m-d')
                    );
            } catch (\Throwable $e) {
            }
            $this->get('mbh.channelmanager')->updatePricesInBackground($minDate, $maxDate);
        }

        $this->get('mbh.cache')->clear('price_cache');

        $resultUpdate->addFlashBag($request);

        return $this->redirect($this->generateUrl('price_cache_overview', [
            'begin'     => $request->get('begin'),
            'end'       => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
            'tariffs'   => $request->get('tariffs'),
        ]));
    }

    /**
     * @param Request $request
     * @param PriceCacheResultUpdate $resultUpdate
     * @param array $dates
     */
    private function handlerUpdatePriceCache(Request $request, PriceCacheResultUpdate $resultUpdate, array &$dates): void
    {
        $updateData = $request->get('updatePriceCaches') ?? [];

        if ($updateData === []) {
            return;
        }

        $validator = $this->get('validator');

        $countUpdate = 0;
        $countRemove = 0;
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
                $dates[] = $priceCache->getDate();
                $countRemove++;
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
            $isSame = $priceCache->isSamePriceCaches($newPriceCache);

            if ($withoutErrorsAtUpdate && !$isSame) {
                $this->dm->persist($newPriceCache);
                $priceCache->setCancelDate(new \DateTime(), true);
                $countUpdate++;
            } elseif(!$withoutErrorsAtUpdate) {
                $this
                    ->container
                    ->get('logger')
                    ->error('Error at update price cache.', iterator_to_array($errorsArUpdate));
                $resultUpdate
                    ->addSkippedDaysAtUpdate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_ERROR, $newPriceCache->getDate()));
            } elseif ($isSame) {
                $resultUpdate
                    ->addSkippedDaysAtUpdate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_SAME, $newPriceCache->getDate()));
            }

            $dates[] = $newPriceCache->getDate();
        }

        $resultUpdate->setAmountRemove($countRemove);
        $resultUpdate->setAmountUpdate($countUpdate);

        $this->dm->flush();
    }

    /**
     * @param Request $request
     * @param PriceCacheResultUpdate $resultUpdate
     * @param array $dates
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function handlerNewPriceCache(Request $request, PriceCacheResultUpdate $resultUpdate ,array &$dates): void
    {
        $newData = $request->get('newPriceCaches') ?? [];

        if ($newData === []) {
            return;
        }

        $validator = $this->get('validator');

        $availableTariffs = $this->helper->toIds(
            $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms')
        );

        $countNew = 0;
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

                    if ($errorsAtCreate->count() === 0) {
                        $this->dm->persist($newPriceCache);
                        $countNew++;
                    } else {
                        $this
                            ->container
                            ->get('logger')
                            ->error('Error at create price cache.', iterator_to_array($errorsAtCreate));
                        $resultUpdate
                            ->addSkippedDaysAtCreate(new PriceCacheSkippingDate(PriceCacheSkippingDate::REASON_ERROR, $newPriceCache->getDate()));
                    }

                    $dates[] = $newPriceCache->getDate();
                }
            }
        }

        $resultUpdate->setAmountCreate($countNew);

        $this->dm->flush();
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
     * @Route("/generator", name="price_cache_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template("MBHPriceBundle:PriceCache:generator.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generatorSaveAction(Request $request)
    {
        $holderDataForm = new PriceCacheHolderDataGeneratorForm();
        $holderDataForm->setHotel($this->hotel);

        $form = $this->createForm(PriceCacheGeneratorType::class, $holderDataForm, [
            'useCategories' => $this->manager->useCategories
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var PriceCacheHolderDataGeneratorForm $holderDataForm */
            $holderDataForm = $form->getViewData();

            /** @var Session $session */
            $session = $request->getSession();

            if ($holderDataForm->isSaveForm()) {
                $session->set('priceCacheGeneratorForm', serialize($holderDataForm));
            } else {
                $session->remove('priceCacheGeneratorForm');
            }

            /** @var PriceCacheResultUpdate $resultUpdate */
            $resultUpdate = $this->get('mbh.price.cache')->update($holderDataForm);

            $this->get('mbh.channelmanager')->updatePricesInBackground($holderDataForm->getBegin(), $holderDataForm->getEnd());
            $this->get('mbh.cache')->clear('price_cache');

            $resultUpdate->addFlashBag($request, true);

            return $this->isSavedRequest() ?
                $this->redirectToRoute('price_cache_generator') :
                $this->redirectToRoute('price_cache_overview');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
