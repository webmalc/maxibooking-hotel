<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Lib\Searchable;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PackageCreationException;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

/**
 *  OrderManager service
 */
class OrderManager implements Searchable
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    /**
     * @var \Symfony\Component\Validator\Validator;
     */
    protected $validator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->helper = $container->get('mbh.helper');
        $this->validator = $container->get('validator');
        $this->flashBag = $container->get('session')
            ->getFlashBag();
    }


    /**
     * @param Package $old
     * @param Package $new
     * @param Tariff $updateTariff
     * @return Package|string
     */
    public function updatePackage(Package $old, Package $new, Tariff $updateTariff = null)
    {
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        //check changes
        if ($old->getBegin() == $new->getBegin() &&
            $old->getEnd() == $new->getEnd() &&
            $old->getRoomType()->getId() == $new->getRoomType()->getId() &&
            $old->getAdults() == $new->getAdults() &&
            $old->getChildren() == $new->getChildren() &&
            $old->getPromotion() == $new->getPromotion() &&
            $old->getSpecial() == $new->getSpecial() &&
            $old->getIsForceBooking() == $new->getIsForceBooking() &&
            ($updateTariff == null || $updateTariff->getId() == $old->getTariff()->getId())
        ) {
            if ($new->getPackagePrice() != $old->getPackagePrice()) {
                $this->updatePricesByDate($new, $updateTariff);
            }

            return $new;
        }

        //search for packages
        $tariff = $updateTariff ?? $new->getTariff();
        $promotion = $new->getPromotion() ? $new->getPromotion() : null;
        $promotion = $promotion ?? $tariff->getDefaultPromotion();
        $oldEnd = clone $old->getEnd();

        $query = new SearchQuery();
        $query->begin = $new->getBegin();
        $query->end = $new->getEnd();
        $query->adults = $new->getAdults();
        $query->children = $new->getChildren();
        $query->tariff = $tariff;
        $query->addRoomType($new->getRoomType()->getId());
        $query->addExcludeRoomType($old->getRoomType()->getId());
        $query->excludeBegin = $old->getBegin();
        $query->excludeEnd = $oldEnd->modify('-1 day');
        $query->forceRoomTypes = true;
        $query->setPromotion($promotion);
        $query->forceBooking = $new->getIsForceBooking();
        $query->setSpecial($new->getSpecial());
        $query->memcached = false;
        $query->setExcludePackage($new);
        $query->setSave(true);

        $results = $this->container->get('mbh.package.search')->search($query);

        if (count($results) == 1) {
            $new->setTariff($results[0]->getTariff())
                ->setPromotion($promotion)
            ;
            //recalculate cache
            $this->container->get('mbh.room.cache')->recalculate(
                $old->getBegin(),
                $oldEnd,
                $old->getRoomType(),
                $old->getTariff(),
                false
            );
            $end = clone $new->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $new->getBegin(),
                $end->modify('-1 day'),
                $new->getRoomType(),
                $new->getTariff()
            );

            $new->setPrice($results[0]->getPrice($results[0]->getAdults(), $results[0]->getChildren()))
                ->setPricesByDate($results[0]->getPricesByDate($results[0]->getAdults(), $results[0]->getChildren()))
                ->setPrices($results[0]->getPackagePrices($results[0]->getAdults(), $results[0]->getChildren()))
                ->setVirtualRoom($results[0]->getVirtualRoom())
            ;

            $new = $this->recalculateServices($new);
            if ($searchQueryId = $results[0]->getQueryId()) {
                $searchQuery = $this->dm->find(SearchQuery::class, $searchQueryId);
                $new->addSearchQuery($searchQuery);
            }

            $begin = $new->getBegin() > $old->getBegin() ? $old->getBegin() : $new->getBegin();
            $end = $new->getEnd() > $old->getEnd() ? $new->getEnd() : $old->getEnd();

            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($begin, $end);

            return $new;
        }

        return 'controller.packageController.record_edited_fail';
    }

    public function tryUpdateAccommodations(Package $package, Package $oldPackage)
    {
        $isSuccessFull = true;
        $dangerNotifications = [];
        if ($package->getRoomType()->getId() !== $oldPackage->getRoomType()->getId()) {
            foreach ($package->getAccommodations() as $accommodation) {
                $this->dm->remove($accommodation);
            }
            $package->removeAccommodations();
            $dangerNotifications[] = 'mbhpackagebundle.services.ordermanager.all_accommodations_removed';
        } elseif ($package->getBegin() != $oldPackage->getBegin() || $package->getEnd() != $oldPackage->getEnd()) {
            $sortedAccommodations = $package->getSortedAccommodations();
            if ($sortedAccommodations->count() > 0) {
                /** @var PackageAccommodation $firstAccommodation */
                $firstAccommodation = $sortedAccommodations->first();
                if ($firstAccommodation->getBegin() != $package->getBegin()) {
                    $firstAccommodation->setBegin($package->getBegin());
                    $errorMessage = $this->checkEditedAccommodation($firstAccommodation, $package);
                    if (!empty($errorMessage)) {
                        $isSuccessFull = false;
                        $dangerNotifications[] = $errorMessage;
                    }
                }

                /** @var PackageAccommodation $lastAccommodation */
                $lastAccommodation = $sortedAccommodations->last();
                if ($lastAccommodation->getEnd() != $package->getEnd()) {
                    $lastAccommodation->setEnd($package->getEnd());
                    $errorMessage = $this->checkEditedAccommodation($lastAccommodation, $package);
                    if (!empty($errorMessage)) {
                        $isSuccessFull = false;
                        $dangerNotifications[] = $errorMessage;
                    }
                }
            }
        }

        return [
            'success' => $isSuccessFull,
            'dangerNotifications' => $dangerNotifications
        ];
    }

    private function checkEditedAccommodation(PackageAccommodation $accommodation, Package $package)
    {
        if (!$accommodation->isAutomaticallyChangeable()) {
            return $this->container
                ->get('translator')
                ->trans('accommodation_manipulator.error.accommodation_is_not_moveable', [
                    '%roomName%' => $accommodation->getName(),
                    '%beginDate%' => $accommodation->getBegin()->format('d.m.Y'),
                    '%endDate%' => $accommodation->getEnd()->format('d.m.Y')
                ]);
        }

        return $this->container
            ->get('mbh_bundle_package.services.package_accommodation_manipulator')
            ->checkErrors($accommodation, $package);
    }

    /**
     * recalculate services while package update
     *
     * @param Package $package
     * @return Package
     */
    private function recalculateServices(Package $package): Package
    {
        $services = $package->getServicesForRecalc();
        // Move services
        foreach ($services as $service) {
            $service->setBegin(null)->setEnd(null)->setUpdatedAt(new \DateTime());
            $this->dm->persist($service);
        }
        $this->dm->flush();
        if (count($services)) {
            $this->flashBag->add('warning', 'controller.packageController.record_edited_success_services');
        }
        return $package;
    }

    /**
     * @param array $data
     * @param Order|null $order
     * @param null $user
     * @param null $cash
     * @return Order
     * @throws Exception
     */
    public function createPackages(array $data, Order $order = null, $user = null, $cash = null)
    {
        if (empty($data['packages'])) {
            throw new Exception('Create packages error: $data["packages"] is empty.');
        }

        if (!is_null($order) && !empty($order->getDeletedAt())) {
            throw new Exception('The specified order is deleted.');
        }

        $tourist = null;
        // create tourist
        if (!empty($data['tourist'])) {
            if (is_array($data['tourist'])) {
                /** @var Tourist $tourist */
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $data['tourist']['lastName'],
                    $data['tourist']['firstName'],
                    null,
                    $this->helper->getDateFromString($data['tourist']['birthday']),
                    $data['tourist']['email'],
                    $data['tourist']['phone']
                );
            } else {
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->find($data['tourist']);
            }

            if (empty($tourist)) {
                throw new Exception('Tourist error: tourist not found.');
            }
        } elseif (!$this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()->isCanBookWithoutPayer()) {
            throw new Exception('Can not create order without payer.');
        }

        if (isset($data['onlineFormId'])) {
            $onlineFormId = $data['onlineFormId'];
            $formConfig = $this->dm->find('MBHOnlineBundle:FormConfig', $onlineFormId);
            if (is_null($formConfig)) {
                throw new Exception('Order creation error: form config with ID = "' . $onlineFormId . '" not found!');
            }

            $touristData = $data['tourist'];
            if ($this->isTouristDataFilled($formConfig, $touristData)) {
                $this->fillTouristData($touristData, $formConfig, $tourist);
            } else {
                throw new Exception('Order creation error: mandatory tourist data not filled!');
            }
        }

        // create order
        if (!$order) {
            if (empty($data['status']) || !isset($data['confirmed'])) {
                throw new Exception('Create order error: $data["status"] || $data["confirmed"] is empty.');
            }
            $order = new Order();
            $order->setConfirmed($data['confirmed'])
                ->setStatus($data['status'])
                ->setOnlinePaymentType(!empty($data['onlinePaymentType']) ? $data['onlinePaymentType'] : null)
                ->setNote(!empty($data['order_note']) ? $data['order_note'] : null);
            if (!empty($tourist)) {
                $order->setMainTourist($tourist);
                $tourist->addOrder($order);
                $this->dm->persist($tourist);
            }

            if (count($this->validator->validate($order))) {
                throw new Exception('Create order error: validation errors.');
            }

            if (($data['status'] == 'offline'
                    && $this->container->get('security.token_storage')->getToken()
                    && $this->container->get('security.authorization_checker')->isGranted('ROLE_ORDER_AUTO_CONFIRMATION')) ||
                ($user instanceof User && in_array('ROLE_ORDER_AUTO_CONFIRMATION', $user->getRoles()))
            ) {
                $order->setConfirmed(true);
            }

            $this->dm->persist($order);
            $this->dm->flush();

            //Acl
            if ($user) {
                $aclProvider = $this->container->get('security.acl.provider');
                $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($order));
                $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_MASTER);
                $aclProvider->updateAcl($acl);
            }
        }

        // create cash document
        if (!empty($cash)) {
            $cashDocument = new CashDocument();
            $cashDocument->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod(isset($cash['method']) ? $cash['method'] : 'electronic')
                ->setOperation(isset($cash['operation']) ? $cash['operation'] : 'in')
                ->setOrder($order)
                ->setTouristPayer($order->getMainTourist())
                ->setTotal(isset($cash['total']) ? (float)$cash['total'] : $order->getPrice());

            if (count($this->validator->validate($order))) {
                throw new Exception('Create cash document error: validation errors.');
            }

            $order->addCashDocument($cashDocument);
            $this->dm->persist($cashDocument);
            $this->dm->persist($order);
            $this->dm->flush();
        }

        // create packages
        foreach ($data['packages'] as $packagesData) {
            $this->createPackage($packagesData, $order, $user);
        }

        //create services
        if (!empty($data['services'])) {
            $this->createServices($data['services'], $order);
        }

        return $order;
    }

    /**
     * @param FormConfig $config
     * @param $touristData
     * @return bool
     */
    private function isTouristDataFilled(FormConfig $config, $touristData)
    {
        return (!$config->isRequestInn() || isset($touristData['inn']))
            && (!$config->isRequestPatronymic() || isset($touristData['patronymic']))
            && (!$config->isRequestTouristDocumentNumber() || isset($touristData['documentNumber']));
    }

    /**
     * @param array $touristRawData
     * @param FormConfig $config
     * @param Tourist $tourist
     */
    private function fillTouristData(array $touristRawData, FormConfig $config, Tourist $tourist)
    {
        if ($config->isRequestInn()) {
            $tourist->setInn($touristRawData['inn']);
        }
        if ($config->isRequestTouristDocumentNumber()) {
            $documentRelation = new DocumentRelation();
            $documentRelation->setNumber($touristRawData['documentNumber']);
            $tourist->setDocumentRelation($documentRelation);
        }
        if ($config->isRequestPatronymic()) {
            $tourist->setPatronymic($touristRawData['patronymic']);
        }
    }

    /**
     * @param array $data
     * @param Order $order
     * @return Order
     * @throws Exception
     */
    public function createServices(array $data, Order $order)
    {
        foreach ($data as $info) {
            if (empty($info['id']) || empty($info['amount'])) {
                throw new Exception('Create services error: $data["id"] || $data["amount"] is empty.');
            }

            $service = $this->dm->getRepository('MBHPriceBundle:Service')->find($info['id']);

            if (!$service) {
                throw new Exception('Create services error: service not found.');
            }

            //find package
            foreach ($order->getPackages() as $package) {
                if ($package->getTariff()->getHotel()->getId() == $service->getCategory()->getHotel()->getId()) {
                    /** @var Package $package */
                    $package = $this->dm->getRepository('MBHPackageBundle:Package')->find($package->getId());

                    if ($service->getCalcType() == 'day_percent') {
                        $date = null;
                        if ($service->getCode() === 'Early check-in') {
                            $date = $package->getBegin();
                        } elseif ($service->getCode() === 'Late check-out') {
                            $date = (clone $package->getEnd())->modify('-1 day');
                        }

                        $price = $package->getPriceByDate($date) * $service->getPrice() / 100;
                    } else {
                        $price = $service->getPrice();
                    }

                    $packageService = new PackageService();
                    $packageService->setPackage($package)
                        ->setService($service)
                        ->setAmount((int)$info['amount'])
                        ->setPrice($price);

                    $package->addService($packageService);

                    $this->dm->persist($packageService);
                    $this->dm->flush();

                    break 1;
                }
            }
        }

        return $order;
    }

    /**
     * @param array $data
     * @param Order $order
     * @param null $user
     * @return Package
     * @throws PackageCreationException
     */
    public function createPackage(array $data, Order $order, $user = null)
    {
        if (!$data['begin'] ||
            !$data['end'] ||
            !$data['adults'] === null ||
            !$data['children'] === null ||
            !$data['roomType']
        ) {
            throw new PackageCreationException(
                $order,
                'Create package error: $data["begin"] || $data["end"] || $data["adults"] || $data["children"] || $data["roomType"] is empty.'
            );
        }

        //search for packages
        $query = new SearchQuery();
        $query->begin = $this->helper->getDateFromString($data['begin']);
        $query->end = $this->helper->getDateFromString($data['end']);
        $query->adults = (int)$data['adults'];
        $query->children = (int)$data['children'];
        $query->tariff = !empty($data['tariff']) ? $data['tariff'] : null;
        $query->isOnline = !empty($data['isOnline']);
        $query->addRoomType($data['roomType']);
        $query->accommodations = (boolean)$data['accommodation'];
        $query->forceRoomTypes = true;
        $query->forceBooking = !empty($data['forceBooking']);
        $query->memcached = false;
        $query->childrenAges = $data['childrenAges'] ?? null;
        if (!empty($data['special'])) {
            $query->setSpecial($this->dm->getRepository('MBHPriceBundle:Special')->find($data['special']));
        }

        $results = $this->container->get('mbh.package.search')->search($query);

        if (count($results) != 1) {
            throw new PackageCreationException(
                $order,
                'Create package error: invalid search results: ' . count($results)
            );
        }

        if ($user
            && $this->container->get('security.token_storage')->getToken()
            && !$this->container->get('mbh.hotel.selector')->checkPermissions($results[0]->getRoomType()->getHotel())) {
            throw new PackageCreationException($order, 'Acl error: permissions denied');
        }

        //create package
        $package = new Package();
        $package->setBegin($results[0]->getBegin())
            ->setEnd($results[0]->getEnd())
            ->setAdults($results[0]->getAdults())
            ->setChildren($results[0]->getChildren())
            ->setTariff($results[0]->getTariff())
            ->setRoomType($results[0]->getRoomType())
            ->setNote(!empty($data['note']) ? $data['note'] : null)
            ->setArrivalTime(!empty($data['arrivalTime']) ? $data['arrivalTime'] : null)
            ->setDepartureTime(!empty($data['departureTime']) ? $data['departureTime'] : null)
            ->setChannelManagerId(!empty($data['channelManagerId']) ? $data['channelManagerId'] : null)
            ->setChannelManagerType(!empty($data['channelManagerType']) ? $data['channelManagerType'] : null)
            ->setOrder($order)
            ->setVirtualRoom($results[0]->getVirtualRoom())
            ->setPrice(
                (isset($data['price'])) ? (int)$data['price'] : $results[0]->getPrice(
                    $results[0]->getAdults(),
                    $results[0]->getChildren()
                )
            )
            ->setPricesByDate($results[0]->getPricesByDate($results[0]->getAdults(), $results[0]->getChildren()))
            ->setPrices($results[0]->getPackagePrices($results[0]->getAdults(), $results[0]->getChildren()))
            ->setIsForceBooking($results[0]->getForceBooking());



        //set isCheckIn
        if ($package->getFirstAccommodation() && $package->getBegin() == new \DateTime('midnight')) {
            $package->setIsCheckIn(true)->setArrivalTime(new \DateTime());
        }

        // add MainTourist
        $tourist = $order->getMainTourist();
        if ($tourist && empty($data['excludeMainTourist'])) {
            $package->addTourist($tourist);
            $tourist->addPackage($package);
            $this->dm->persist($tourist);
        }

        // add Tourists
        if (!empty($data['tourists']) && is_array($data['tourists'])) {
            foreach ($data['tourists'] as $info) {
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $info['lastName'],
                    $info['firstName'],
                    null,
                    $this->helper->getDateFromString($info['birthday']),
                    $info['email'],
                    $info['phone']
                );
                $package->addTourist($tourist);
                $tourist->addPackage($package);
                $this->dm->persist($tourist);
            }
        }

        if (count($this->validator->validate($package))) {
            throw new PackageCreationException($order, 'Create package error: validation errors.');
        }

        foreach ($package->getTariff()->getDefaultServices() as $tariffService) {
            if (!$tariffService->getService() || !$tariffService->getAmount()) {
                continue;
            }

            $tariffService->getPersons() ?
                $persons = $tariffService->getPersons() : $persons = $package->getAdults() + $package->getChildren();

            $tariffService->getNights() ?
                $nights = $tariffService->getNights() : $nights = $package->getNights();

            //transform TariffService to PackageService
            $packageService = new PackageService();
            $defaultService = $tariffService->getService();
            $packageService
                ->setService($defaultService)
                ->setAmount($tariffService->getAmount())
                ->setPersons($persons)
                ->setNights($nights)
                ->setPrice(0)
                ->setIncludeArrival($defaultService->isIncludeArrival())
                ->setIncludeDeparture($defaultService->isIncludeDeparture())
                ->setRecalcWithPackage(
                    $defaultService->isRecalcWithPackage()
                )
                ->setPackage($package)
                ->setNote($this->container->get('translator')->trans('order_manager.package_service_comment.default_service'));

            $package->addService($packageService);
            $this->dm->persist($packageService);
        }

        $order->addPackage($package);
        $this->dm->persist($order);
        $this->dm->persist($package);
        $this->dm->flush();

        if ($query->getSpecial()) {
            $package->setSpecial($query->getSpecial());

            if (count($this->validator->validate($package))) {
                $this->dm->remove($package);
                $this->dm->flush();
                throw new PackageCreationException($order, 'Create package error: validation errors.');
            }
            $this->dm->persist($package);
            $this->dm->flush();
        }

        //accommodation
        if ($query->accommodations) {
            $room = $this->dm->getRepository('MBHHotelBundle:Room')->find($data['accommodation']);
            if (!$room) {
                throw new PackageCreationException($order, 'Create package error: accommodation not found.');
            }
            $packageAccommodation = new PackageAccommodation();
            $packageAccommodation->setBegin($package->getBegin())->setEnd($package->getEnd())->setAccommodation($room);
            $package->addAccommodation($packageAccommodation);

            $this->dm->persist($packageAccommodation);
            $this->dm->flush();
        }

        //Acl
        if ($user) {
            $aclProvider = $this->container->get('security.acl.provider');
            $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($package));
            $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_MASTER);
            $aclProvider->updateAcl($acl);
        }

        //Add cash docs
        if (!empty($data['paid'])) {
            $cashIn = new CashDocument();
            $cashIn->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTotal($package->getPrice())
                ->setIsConfirmed(true);
            $order->addCashDocument($cashIn);
            $this->dm->persist($order);
            $this->dm->persist($cashIn);
            $this->dm->flush();
        }

        if (!empty($data['fee'])) {
            $cashOut = new CashDocument();
            $cashOut->setMethod('electronic')
                ->setOperation('fee')
                ->setOrder($order)
                ->setTotal((int)$data['fee'])
                ->setNote('fee')
                ->setIsConfirmed(true);
            $order->addCashDocument($cashOut);
            $this->dm->persist($order);
            $this->dm->persist($cashOut);
            $this->dm->flush();
        }

        //add infants
        if (!empty($data['infants']) && (int)$data['infants'] > 0) {
            $service = $this->dm->getRepository('MBHPriceBundle:Service')->findOneByCode($package->getTariff(), 'Infant');

            if ($service) {
                $infantService = new PackageService();
                $infantService
                    ->setAmount(1)
                    ->setNights($package->getNights())
                    ->setPersons((int)$data['infants'])
                    ->setService($service)
                    ->setPackage($package)
                    ->setPrice($service->getPrice());
                $package->addService($infantService);
                $this->dm->persist($package);
                $this->dm->persist($infantService);
                $this->dm->flush();
            }

        }

        //inject SearchQuery
        if (isset($data['savedQueryId']) && (null !== $data['savedQueryId'])) {
            $searchQuery = $this->dm->find('MBHPackageBundle:SearchQuery', $data['savedQueryId']);
            $package->addSearchQuery($searchQuery);
            $this->dm->flush();
        }

        return $package;
    }

    public function updatePricesByDate(Package $package, ?Tariff $tariff)
    {
        $newDailyPrice = $package->getPackagePrice() / $package->getNights();
        $newPricesByDate = [];
        $begin = clone $package->getBegin();
        $end = clone $package->getEnd();
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            $newPricesByDate[$day->format('d_m_Y')] = $newDailyPrice;
            $packagePrice = $package->getPackagePriceByDate($day);
            if (is_null($packagePrice)) {
                $prices =  $package->getPrices()->toArray();
                if (!empty($prices)) {
                    $firstPackagePrice = current($prices);
                    $packagePrice = clone $firstPackagePrice;
                    $packagePrice->setDate($day);
                } else {
                    $packagePrice = new PackagePrice($day, $newDailyPrice, $tariff ? $tariff : $package->getTariff());
                }
                $package->addPackagePrice($packagePrice);
            }
            $packagePrice->setPrice($newDailyPrice);
            if (!is_null($tariff)) {
                $packagePrice->setTariff($tariff);
            }
        }
        $package->setPricesByDate($newPricesByDate);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param Hotel $hotel
     * @return Builder
     */
    public function getQueryBuilderByRequestData(Request $request, User $user, Hotel $hotel)
    {
        $data = [
            'hotel' => $hotel,
            'roomType' => $this->helper->getDataFromMultipleSelectField($request->get('roomType')),
            'status' => $request->get('status'),
            'deleted' => $request->get('deleted'),
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'dates' => $request->get('dates'),
            'skip' => $request->get('start'),
            'limit' => $request->get('length'),
            'query' => $request->get('search')['value'],
            'order' => $request->get('order')['0']['column'],
            'dir' => $request->get('order')['0']['dir'],
            'paid' => $request->get('paid'),
            'confirmed' => $request->get('confirmed'),
            'source' => $request->get('source')
        ];

        //quick links
        switch ($request->get('quick_link')) {
            case 'begin-today':
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $data['begin'] = $now->format('d.m.Y');
                $data['end'] = $now->format('d.m.Y');
                $data['checkOut'] = false;
                $data['checkIn'] = false;
                break;

            case 'begin-tomorrow':
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $now->modify('+1 day');
                $data['begin'] = $now->format('d.m.Y');
                $data['end'] = $now->format('d.m.Y');
                $data['checkOut'] = false;
                $data['checkIn'] = false;
                break;

            case 'live-now':
                $data['filter'] = 'live_now';
                $data['checkIn'] = true;
                $data['checkOut'] = false;
                break;

            case 'without-approval':
                $data['confirmed'] = '0';
                break;

            case 'without-accommodation':
                $data['filter'] = 'without_accommodation';
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $data['end'] = $now->format('d.m.Y');
                break;

            case 'not-paid':
                $data['paid'] = 'not_paid';
                break;

            case 'not-paid-time':
                /** @var ClientConfig $clientConfig */
                $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
                $notPaidTime = new \DateTime('-' . $clientConfig->getNumberOfDaysForPayment().'days');
                $data['paid'] = 'not_paid';
                $data['dates'] = 'createdAt';
                $data['end'] = $notPaidTime->format('d.m.Y');
                break;

            case 'not-check-in':
                $data['checkIn'] = false;
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $data['end'] = $now->format('d.m.Y');
                break;

            case 'created-by':
                $data['createdBy'] = $user->getUsername();
                break;
            default:
        }

        //List user package only
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW_ALL')) {
            $data['createdBy'] = $user->getUsername();
        }

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        return $packageRepository->fetchQuery($data);
    }

    /**
     * @param Builder $qb
     * @return array
     */
    public function calculateSummary(Builder $qb)
    {
        $packages = $qb
            ->hydrate(false)
            ->select(['order', '_id', 'adults', 'children', 'begin', 'end', 'price', 'totalOverwrite', 'servicesPrice', 'discount', 'isPercentDiscount'])
            ->getQuery()
            ->execute()
            ->toArray()
        ;

        $orderIds = array_map(function ($package) {
            return $package['order']['$id'];
        }, $packages);
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        $orders = $orderRepository
            ->createQueryBuilder()
            ->field('id')->in($orderIds)
            ->hydrate(false)
            ->select(['id', 'paid', 'price'])
            ->getQuery()
            ->execute()
            ->toArray();

        $numberOfNights = 0;
        $numberOfGuests = 0;
        $totalSum = 0;
        $paidSum = 0;
        $debt = 0;
        $oneDay = 24*60*60;
        foreach ($packages as $rawPackageData) {
            $numberOfGuests += $rawPackageData['children'] + $rawPackageData['adults'];

            /** @var \MongoDate $mongoBegin */
            $mongoBegin = $rawPackageData['begin'];
            /** @var \MongoDate $mongoEnd */
            $mongoEnd = $rawPackageData['end'];
            $numberOfNights += round(($mongoEnd->sec - $mongoBegin->sec) / $oneDay);

            if(isset($rawPackageData['totalOverwrite'])) {
                $price = $rawPackageData['totalOverwrite'];
            } else {
                $price = $packagePrice = isset($rawPackageData['price']) ? $rawPackageData['price'] : 0;
                if (isset($rawPackageData['servicesPrice'])) {
                    $price += $rawPackageData['servicesPrice'];
                }
                if (isset($rawPackageData['discount'])) {
                    $discount = isset($rawPackageData['isPercentDiscount']) && $rawPackageData['isPercentDiscount']
                        ? $packagePrice * $rawPackageData['discount']/100
                        : $rawPackageData['discount'];
                    $price -= $discount;
                }
            }
            $totalSum += $price;

            $rawOrderData = $orders[$rawPackageData['order']['$id']];
            if ($rawOrderData['price'] != 0) {
                $packagePriceToOrderPriceRelation = $price / $rawOrderData['price'];
                $packagePayment = $rawOrderData['paid'] * $packagePriceToOrderPriceRelation;
                $paidSum += $packagePayment;
                $debt += ($price - $packagePayment);
            }
        }

        return [
            'total' => $totalSum,
            'paid' => $paidSum,
            'debt' => $debt,
            'nights' => $numberOfNights,
            'guests' => $numberOfGuests,
        ];
    }
}