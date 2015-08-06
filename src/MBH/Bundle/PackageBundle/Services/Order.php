<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Document\Order as OrderDoc;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 *  Order service
 */
class Order
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


    public function updatePackage(Package $old, Package $new)
    {
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        //check changes
        if (
            $old->getBegin() == $new->getBegin() &&
            $old->getEnd() == $new->getEnd() &&
            $old->getRoomType()->getId() == $new->getRoomType()->getId()
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
            ;

            $this->container->get('mbh.channelmanager')->updateRoomsInBackground($new->getBegin(), $new->getEnd());

            return $new;
        }

        return 'controller.packageController.record_edited_fail';
    }

    /**
     * @param array $data
     * @param OrderDoc $order
     * @param bool $user
     * @param array $cash
     * @return OrderDoc
     * @throws \Exception
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function createPackages(array $data, OrderDoc $order = null, $user = null, $cash = null)
    {
        if (empty($data['packages'])) {
            throw new \Exception('Create packages error: $data["packages"] is empty.');
        }

        // create tourist
        if (!empty($data['tourist'])) {
            $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $data['tourist']['lastName'],
                $data['tourist']['firstName'],
                null,
                $this->helper->getDateFromString($data['tourist']['birthday']),
                $data['tourist']['email'],
                $data['tourist']['phone']
            );

            if (empty($tourist)) {
                throw new \Exception('Tourist error: tourist not found.');
            }
        }

        // create order
        if (!$order) {
            if  (empty($data['status']) || !isset($data['confirmed'])) {
                throw new \Exception('Create order error: $data["status"] || $data["confirmed"] is empty.');
            }
            $order = new OrderDoc();
            $order->setConfirmed($data['confirmed'])
                ->setStatus($data['status'])
                ->setNote(!empty($data['order_note']) ? $data['order_note'] : null)
            ;
            if (!empty($tourist)) {
                $order->setMainTourist($tourist);
                $tourist->addOrder($order);
                $this->dm->persist($tourist);
            }

            if (!$this->validator->validate($order)) {
                throw new \Exception('Create order error: validation errors.');
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
                ->setTotal(isset($cash['total']) ? (float)$cash['total'] : $order->getPrice())
            ;

            if (!$this->validator->validate($order)) {
                throw new \Exception('Create cash document error: validation errors.');
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
     * @param OrderDoc $order
     * @return OrderDoc
     * @throws \Exception
     */
    public function createServices(array $data, OrderDoc $order)
    {
        foreach ($data as $info) {
            if(empty($info['id']) || empty($info['amount'])) {
                throw new \Exception('Create services error: $data["id"] || $data["amount"] is empty.');
            }

            $service = $this->dm->getRepository('MBHPriceBundle:Service')->find($info['id']);

            if (!$service) {
                throw new \Exception('Create services error: service not found.');
            }

            //find package
            foreach ($order->getPackages() as $package) {
                if ($package->getTariff()->getHotel()->getId() == $service->getCategory()->getHotel()->getId()) {

                    $package = $this->dm->getRepository('MBHPackageBundle:Package')->find($package->getId());

                    $packageService = new PackageService();
                    $packageService->setPackage($package)
                        ->setService($service)
                        ->setAmount((int) $info['amount'])
                        ->setPrice($service->getPrice());

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
     * @param OrderDoc $order
     * @param null $user
     * @return Package
     * @throws \Exception
     * @throws \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function createPackage(array $data, OrderDoc $order, $user = null)
    {
        if (!$data['begin'] ||
            !$data['end'] ||
            !$data['adults'] === null ||
            !$data['children'] === null ||
            !$data['roomType']
        ) {
            throw new \Exception('Create package error: $data["begin"] || $data["end"] || $data["adults"] || $data["children"] || $data["roomType"] is empty.');
        }

        //search for packages
        $query = new SearchQuery();
        $query->begin = $this->helper->getDateFromString($data['begin']);
        $query->end = $this->helper->getDateFromString($data['end']);;
        $query->adults = (int) $data['adults'];
        $query->children = (int) $data['children'];
        $query->tariff = !empty($data['tariff'])  ? $data['tariff'] : null;
        $query->isOnline = !empty($data['isOnline']);
        $query->addRoomType($data['roomType']);

        $results = $this->container->get('mbh.package.search')->search($query);

        if (count($results) != 1) {
            throw new \Exception('Create package error: invalid search results: ' . count($results));
        }

        if ($user && !$this->container->get('mbh.hotel.selector')->checkPermissions($results[0]->getRoomType()->getHotel())) {
            throw new \Exception('Acl error: permissions denied');
        }

        //create package
        $package = new Package();
        $package->setBegin($results[0]->getBegin())
            ->setEnd($results[0]->getEnd())
            ->setAdults($results[0]->getAdults())
            ->setChildren($results[0]->getChildren())
            ->setTariff($results[0]->getTariff())
            ->setRoomType($results[0]->getRoomType())
            ->setNote(!empty($data['note'])  ? $data['note'] : null)
            ->setArrivalTime(!empty($data['arrivalTime'])  ? $data['arrivalTime'] : null)
            ->setDepartureTime(!empty($data['departureTime'])  ? $data['departureTime'] : null)
            ->setChannelManagerId(!empty($data['channelManagerId'])  ? $data['channelManagerId'] : null)
            ->setChannelManagerType(!empty($data['channelManagerType'])  ? $data['channelManagerType'] : null)
            ->setOrder($order)
            ->setPrice(
                (isset($data['price'])) ? (int) $data['price'] : $results[0]->getPrice($results[0]->getAdults(), $results[0]->getChildren())
            )
            ->setPricesByDate($results[0]->getPricesByDate($results[0]->getAdults(), $results[0]->getChildren()))
        ;

        // add MainTourist
        $tourist = $order->getMainTourist();
        if ($tourist && empty($data['excludeMainTourist'])) {
            $package->addTourist($tourist);
            $tourist->addPackage($package);
            $this->dm->persist($tourist);
        }

        // add Tourists
        if(!empty($data['tourists']) && is_array($data['tourists'])) {
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

        if (!$this->validator->validate($package)) {
            throw new \Exception('Create package error: validation errors.');
        }

        $order->addPackage($package);
        $this->dm->persist($order);
        $this->dm->persist($package);
        $this->dm->flush();

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
                ->setIsConfirmed(true)
            ;
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
                ->setTotal((int) $data['fee'])
                ->setNote('fee')
                ->setIsConfirmed(true)
            ;
            $order->addCashDocument($cashOut);
            $this->dm->persist($order);
            $this->dm->persist($cashOut);
            $this->dm->flush();
        }

        return $package;
    }
}
