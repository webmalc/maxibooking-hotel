<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Form\PriceCacheGeneratorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function indexAction()
    {
        return [
            'roomTypes' => $this->manager->getRooms($this->hotel),
            'tariffs' => $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'prices'),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="price_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_VIEW')")
     * @Template()
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

        //get roomTypes
        $roomTypes = $this->manager->getRooms($this->hotel, $request->get('roomTypes'));
        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }
        //get tariffs
        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->fetchChildTariffs($this->hotel, 'prices', $request->get('tariffs'));
        if (!count($tariffs)) {
            return array_merge($response, ['error' => 'Тарифы не найдены']);
        }

        //get priceCaches
        $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
            $begin, $end, $this->hotel,
            $request->get('roomTypes') ? $request->get('roomTypes') : [],
            $request->get('tariffs') ? $request->get('tariffs') : [],
            true,
            $this->manager->useCategories
        );

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
     * @return array
     */
    public function saveAction(Request $request)
    {
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updatePriceCaches'))) ? $updateData = [] : $updateData = $request->get('updatePriceCaches');
        (empty($request->get('newPriceCaches'))) ? $newData = [] : $newData = $request->get('newPriceCaches');
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

                    $newPriceCache = new PriceCache();
                    $newPriceCache->setHotel($this->hotel)
                        ->setCategoryOrRoomType($roomType, $this->manager->useCategories)
                        ->setTariff($tariff)
                        ->setDate($helper->getDateFromString($date))
                        ->setPrice($prices['price'])
                        ->setChildPrice(isset($prices['childPrice']) && $prices['childPrice'] !== '' ? $prices['childPrice'] : null)
                        ->setIsPersonPrice(isset($prices['isPersonPrice']) && $prices['isPersonPrice'] !== '' ? true : false)
                        ->setSinglePrice(isset($prices['singlePrice']) && $prices['singlePrice'] !== '' ? $prices['singlePrice'] : null)
                        ->setAdditionalPrice(isset($prices['additionalPrice']) && $prices['additionalPrice'] !== '' ? $prices['additionalPrice'] : null)
                        ->setAdditionalChildrenPrice(isset($prices['additionalChildrenPrice']) && $prices['additionalChildrenPrice'] !== '' ? $prices['additionalChildrenPrice'] : null);

                    $newPriceCache = $this->addAdditionalPrices($roomType, $newPriceCache, $prices);

                    if ($validator->validate($newPriceCache)) {
                        $this->dm->persist($newPriceCache);
                    }
                }
            }
        }
        $this->dm->flush();

        //update
        foreach ($updateData as $priceCacheId => $prices) {
            $priceCache = $this->dm->getRepository('MBHPriceBundle:PriceCache')->find($priceCacheId);
            if (!$priceCache || $priceCache->getHotel() != $this->hotel) {
                continue;
            }

            //delete
            if (isset($prices['price']) && $prices['price'] === '') {
                $this->dm->remove($priceCache);
                continue;
            }

            $priceCache
                ->setPrice($prices['price'])
                ->setChildPrice(isset($prices['childPrice']) && $prices['childPrice'] !== '' ? $prices['childPrice'] : null)
                ->setIsPersonPrice(isset($prices['isPersonPrice']) ? true : false)
                ->setSinglePrice(isset($prices['singlePrice']) && $prices['singlePrice'] !== '' ? $prices['singlePrice'] : null)
                ->setAdditionalPrice(isset($prices['additionalPrice']) && $prices['additionalPrice'] !== '' ? $prices['additionalPrice'] : null)
                ->setAdditionalChildrenPrice(isset($prices['additionalChildrenPrice']) && $prices['additionalChildrenPrice'] !== '' ? $prices['additionalChildrenPrice'] : null);

            $priceCache = $this->addAdditionalPrices(
                $priceCache->getCategoryOrRoomType($this->manager->useCategories), $priceCache, $prices
            );

            if ($validator->validate($priceCache)) {
                $this->dm->persist($priceCache);
            }
        }
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', 'Изменения успешно сохранены.');

        $this->get('mbh.channelmanager')->updatePricesInBackground();
        $this->get('mbh.cache')->clear();

        return $this->redirect($this->generateUrl('price_cache_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
            'tariffs' => $request->get('tariffs'),
        ]));
    }

    /**
     * @param RoomTypeInterface $roomType
     * @param PriceCache $priceCache
     * @param array $prices
     * @return PriceCache
     */
    private function addAdditionalPrices(RoomTypeInterface $roomType, PriceCache $priceCache, array $prices)
    {
        if ($roomType->getIsIndividualAdditionalPrices() && $roomType->getAdditionalPlaces() > 1) {
            $childrenPrices = $additionalPrices = [];
            for ($i = 1; $i < $roomType->getAdditionalPlaces(); $i++) {
                if (isset($prices['additionalPrice' . $i])) {
                    $additionalPrices[$i] = $prices['additionalPrice' . $i];
                }
                if (isset($prices['additionalChildrenPrice' . $i])) {
                    $childrenPrices[$i] = $prices['additionalChildrenPrice' . $i];
                }

                $priceCache->setAdditionalPrices($additionalPrices);
                $priceCache->setAdditionalChildrenPrices($childrenPrices);
            }
        }

        return $priceCache;
    }

    /**
     * @Route("/generator", name="price_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_PRICE_CACHE_EDIT')")
     * @Template()
     */
    public function generatorAction(Request $request)
    {
        $sessionFormData = [];
        if ($request->getSession()->has('priceCacheGeneratorForm')) {
            $sessionFormData = $request->getSession()->get('priceCacheGeneratorForm');
            foreach ($sessionFormData['roomTypes'] as $id) {
                $sessionFormData['roomTypes'][$id] = $this->dm->getRepository($this->manager->useCategories ? RoomTypeCategory::class : RoomType::class)->find($id);

            }
            foreach ($sessionFormData['tariffs'] as $id) {
                $sessionFormData['tariffs'][$id] = $this->dm->getRepository(Tariff::class)->find($id);
            }
        }

        $form = $this->createForm(new PriceCacheGeneratorType(), $sessionFormData, [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $this->hotel,
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
     * @return array
     */
    public function generatorSaveAction(Request $request)
    {
        $form = $this->createForm(new PriceCacheGeneratorType(), [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $this->hotel,
            'useCategories' => $this->manager->useCategories
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $data = $form->getViewData();

            $session = $request->getSession();

            if ($data['saveForm']) {

                $session->set('priceCacheGeneratorForm', array_merge($data, [
                    'roomTypes' => $this->helper->toIds($data['roomTypes']),
                    'tariffs' => $this->helper->toIds($data['tariffs'])
                ]));
            } else {
                $session->remove('priceCacheGeneratorForm');
            }

            $childrenPrices = [
                0 => $data['additionalChildrenPrice']
            ];
            $additionalPrices = [
                0 => $data['additionalPrice']
            ];

            if (!empty($data['additionalPricesCount'])) {
                for ($i = 1; $i < $data['additionalPricesCount']; $i++) {
                    $additionalPrices[$i] = $data['additionalPrice' . $i];
                    $childrenPrices[$i] = $data['additionalChildrenPrice' . $i];
                }
            }

            $this->get('mbh.price.cache')->update(
                $data['begin'], $data['end'], $this->hotel, $data['price'], $data['isPersonPrice'],
                $data['singlePrice'], $data['additionalPrice'], $data['additionalChildrenPrice'],
                $data['roomTypes']->toArray(), $data['tariffs']->toArray(), $data['weekdays'],
                $data['childPrice'], $additionalPrices, $childrenPrices
            );

            $this->get('mbh.channelmanager')->updatePricesInBackground();
            $this->get('mbh.cache')->clear();

            $session->getFlashBag()->set('success', 'Данные успешно сгенерированы.');

            return $this->isSavedRequest() ?
                $this->redirectToRoute('price_cache_generator') :
                $this->redirectToRoute('price_cache_overview');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
