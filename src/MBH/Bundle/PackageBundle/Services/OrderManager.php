<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 *  OrderManager service
 */
class OrderManager
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
            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($new->getBegin(), $new->getEnd());

            return $new;
        }

        return 'controller.packageController.record_edited_fail';
    }

    public function tryUpdateAccommodations(Package $package, Package $oldPackage)
    {
        $errorMessages = [];
        if ($package->getBegin() != $oldPackage->getBegin() || $package->getEnd() != $oldPackage->getEnd()) {

            $sortedAccommodations = $package->getSortedAccommodations();
            if ($sortedAccommodations->count() > 0) {
                /** @var PackageAccommodation $firstAccommodation */
                $firstAccommodation = $sortedAccommodations->first();
                if ($firstAccommodation->getBegin() != $package->getBegin()) {
                    $firstAccommodation->setBegin($package->getBegin());
                    $errorMessage = $this->checkEditedAccommodation($firstAccommodation, $package);
                    if (!empty($errorMessage)) {
                        $errorMessages[] = $errorMessage;
                    }
                }

                /** @var PackageAccommodation $lastAccommodation */
                $lastAccommodation = $sortedAccommodations->last();
                if ($lastAccommodation->getEnd() != $package->getEnd()) {
                    $lastAccommodation->setEnd($package->getEnd());
                    $errorMessage = $this->checkEditedAccommodation($lastAccommodation, $package);
                    if (!empty($errorMessage)) {
                        $errorMessages[] = $errorMessage;
                    }
                }
            }
        }

        return $errorMessages;
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

        // create tourist
        if (!empty($data['tourist'])) {
            if (is_array($data['tourist'])) {
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

            if (($data['status'] == 'offline' && $this->container->get('security.authorization_checker')->isGranted('ROLE_ORDER_AUTO_CONFIRMATION')) ||
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
     * @param array $data
     * @param Order $order
     * @param null $user
     * @return Package
     * @throws Exception
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

        if ($user && !$this->container->get('mbh.hotel.selector')->checkPermissions($results[0]->getRoomType()->getHotel())) {
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
                ->setNote($this->container->get('translator')->trans('mbhpackagebundle.services.ordermanager.usluga.po.umolchaniyu'));

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

        return $package;
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
                    $package = $this->dm->getRepository('MBHPackageBundle:Package')->find($package->getId());

                    $packageService = new PackageService();
                    $packageService->setPackage($package)
                        ->setService($service)
                        ->setAmount((int)$info['amount'])
                        ->setPrice($service->getPrice());

                    $this->dm->persist($packageService);
                    $this->dm->flush();

                    break 1;
                }
            }
        }

        return $order;
    }

    public function updatePricesByDate(Package $package, ?Tariff $tariff)
    {
        $newDailyPrice = $package->getPrice() / $package->getNights();
        $newPricesByDate = [];
        $begin = clone $package->getBegin();
        $end = clone $package->getEnd();
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $day) {
            $newPricesByDate[$day->format('d_m_Y')] = $newDailyPrice;
            $packagePrice = $package->getPackagePriceByDate($day);
            $packagePrice->setPrice($newDailyPrice);
            if (!is_null($tariff)) {
                $packagePrice->setTariff($tariff);
            }
        }
        $package->setPricesByDate($newPricesByDate);
    }
}

/**
 * Class PackageCreationException
 */
//TODO: Убрать в нужное место!
class PackageCreationException extends Exception
{
    /**
     * @var Order
     */
    public $order;

    public function __construct(Order $order, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->order = $order;
        parent::__construct($message, $code, $previous);
    }
}
