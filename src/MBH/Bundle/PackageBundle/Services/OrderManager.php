<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
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
    }

    /**
     * @param Package $package
     * @param \DateTime $date
     * @return Package
     * @throws Exception
     */
    public function relocatePackage(Package $package, \DateTime $date): Package
    {
        $start = clone $package->getBegin();
        $end = clone $package->getEnd();
        $start->modify('+1 day');
        $end->modify('-1 day');

        if (!$package->getAccommodation()) {
            throw new Exception('controller.packageController.relocation_accommodation_error');
        }
        
        if ($date > $end || $date < $start) {
            throw new Exception('controller.packageController.relocation_dates_error');
        }


        $newPackage = clone $package;
        $newPackage
            ->setBegin($date)
            ->setEnd($package->getEnd())
            ->setPackagePrice(0)
            ->setTotalOverwrite(0)
            ->setPrice(0)
            ->setNumberWithPrefix($package->getNumberWithPrefix() . '_переезд')
            ->setServicesPrice(0)
            ->clearServices()
            ->setAccommodation(null)
        ;

        $package->setEnd($date);
        $this->dm->persist($package);
        $this->dm->flush();

        $this->dm->persist($newPackage);
        $cacheEnd = $newPackage->getEnd();
        $this->dm->flush();

        
        $this->container->get('mbh.room.cache')->recalculate(
            $newPackage->getBegin(), $cacheEnd->modify('-1 day'), $newPackage->getRoomType(), $newPackage->getTariff(), false
        );

        return $newPackage;
    }


    /**
     * @param Package $old
     * @param Package $new
     * @return Package|string
     */
    public function updatePackage(Package $old, Package $new)
    {
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        //check changes
        if (
            $old->getBegin() == $new->getBegin() &&
            $old->getEnd() == $new->getEnd() &&
            $old->getRoomType()->getId() == $new->getRoomType()->getId() &&
            $old->getAdults() == $new->getAdults() &&
            $old->getChildren() == $new->getChildren() &&
            $old->getPromotion() == $new->getPromotion() &&
            $old->getSpecial() == $new->getSpecial() &&
            $old->getIsForceBooking() == $new->getIsForceBooking()
        ) {
            return $new;
        }


        //check accommodation
        $accommodation = $old->getAccommodation();
        if ($accommodation) {
            $rooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
                $new->getBegin(),
                $new->getEnd(),
                $accommodation->getRoomType()->getHotel(),
                $accommodation->getRoomType()->getId(),
                $accommodation->getId(),
                $new->getId(),
                false
            );

            if (!count($rooms)) {
                return 'controller.packageController.record_edited_fail_accommodation';
            }
        }

        //search for packages
        $oldEnd = clone $old->getEnd();
        $query = new SearchQuery();
        $query->begin = $new->getBegin();
        $query->end = $new->getEnd();
        $query->adults = $new->getAdults();
        $query->children = $new->getChildren();
        $query->tariff = $new->getTariff();
        $query->addRoomType($new->getRoomType()->getId());
        $query->addExcludeRoomType($old->getRoomType()->getId());
        $query->excludeBegin = $old->getBegin();
        $query->excludeEnd = $oldEnd->modify('-1 day');
        $query->forceRoomTypes = true;
        $query->setPromotion($new->getPromotion() ? $new->getPromotion() : false);
        $query->forceBooking = $new->getIsForceBooking();
        $query->setSpecial($new->getSpecial());
        $query->memcached = false;

        $results = $this->container->get('mbh.package.search')->search($query);

        if (count($results) == 1) {
            //recalculate cache
            $this->container->get('mbh.room.cache')->recalculate(
                $old->getBegin(), $oldEnd, $old->getRoomType(), $old->getTariff(), false
            );
            $end = $new->getEnd();
            $this->container->get('mbh.room.cache')->recalculate(
                $new->getBegin(), $end->modify('-1 day'), $new->getRoomType(), $new->getTariff()
            );

            $new->setPrice($results[0]->getPrice($results[0]->getAdults(), $results[0]->getChildren()))
                ->setPricesByDate($results[0]->getPricesByDate($results[0]->getAdults(), $results[0]->getChildren()))
                ->setPrices($results[0]->getPackagePrices($results[0]->getAdults(), $results[0]->getChildren()));

            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($new->getBegin(), $new->getEnd());

            return $new;
        }

        return 'controller.packageController.record_edited_fail';
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
            throw new PackageCreationException($order,
                'Create package error: $data["begin"] || $data["end"] || $data["adults"] || $data["children"] || $data["roomType"] is empty.');
        }

        //search for packages
        $query = new SearchQuery();
        $query->begin = $this->helper->getDateFromString($data['begin']);
        $query->end = $this->helper->getDateFromString($data['end']);;
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
            throw new PackageCreationException($order,
                'Create package error: invalid search results: ' . count($results));
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
                (isset($data['price'])) ? (int)$data['price'] : $results[0]->getPrice($results[0]->getAdults(),
                    $results[0]->getChildren())
            )
            ->setPricesByDate($results[0]->getPricesByDate($results[0]->getAdults(), $results[0]->getChildren()))
            ->setPrices($results[0]->getPackagePrices($results[0]->getAdults(), $results[0]->getChildren()))
            ->setIsForceBooking($results[0]->getForceBooking());

        //accommodation
        if ($query->accommodations) {
            $room = $this->dm->getRepository('MBHHotelBundle:Room')->find($data['accommodation']);
            if (!$room) {
                throw new PackageCreationException($order, 'Create package error: accommodation not found.');
            }
            $package->setAccommodation($room);
        }

        //set isCheckIn
        if ($package->getAccommodation() && $package->getBegin() == new \DateTime('midnight')) {
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
            $packageService
                ->setService($tariffService->getService())
                ->setAmount($tariffService->getAmount())
                ->setPersons($persons)
                ->setNights($nights)
                ->setPrice(0)
                ->setPackage($package)
                ->setNote('Услуга по умолчанию');

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