<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PackageBundle\Document\MovingPackageData;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageMovingInfo;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\OrderManager;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PackageZip
{
    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var ValidatorInterface;
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var SearchFactory
     */
    private $packageSearch;

    /**
     * @var Helper
     */
    private $helper;

    const BEGIN = 'midnight';

    const END = '+365 days';

    public function __construct(
        DocumentManager $dm,
        ContainerInterface $container,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        OrderManager $orderManager,
        SearchFactory $packageSearch,
        Helper $helper
    )
    {
        $this->dm = $dm;
        $this->container = $container;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->orderManager = $orderManager;
        $this->packageSearch = $packageSearch;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function packagesZip()
    {
        $this->logger->alert('---------START---------');
        $roomTypeZipConfig = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();
        $roomTypesByCategories = $this->roomTypeByCategories($roomTypeZipConfig);

        $skip = 300;
        $info['amount'] = 0;
        $info['error'] = 0;
        $packagesCount = $this->getMaxAmountPackages($roomTypesByCategories);

        for ($i = 0; $i <= ceil($packagesCount / $skip); $i++) {
            $packages = $this->getPackages($roomTypesByCategories, $skip, $i);

            /** @var Package $package */
            foreach ($packages as $package) {
                $oldRoomType = $package->getRoomType();
                try {
                    if ($package->getCountPersons() < $package->getRoomType()->getTotalPlaces()
                        && $package->getIsMovable()
                        && $this->hasLessAvailability($package->getRoomType())) {

                        $this->logPackage('---------BEGIN SEARCH OF OPTIMAL ROOM TYPE---------', $package, $package->getRoomType());

                        $optimalRoomType = $this->getOptimalRoomType($package);
                        if (!is_null($optimalRoomType) && $optimalRoomType->getId() !== $oldRoomType->getId()) {
                            $result = $this->orderManager->changeRoomType($package, $optimalRoomType);

                            if ($result) {
                                $info['amount']++;
                                $this->logPackage('PACKAGE ROOM TYPE CHANGED', $package, $oldRoomType);
                            } else {
                                $this->logPackage('FOUND ROOM TYPE NOT EMPTY', $package, $oldRoomType);
                            }
                        } else {
                            $this->logPackage('OPTIMAL ROOM TYPE NOT FOUND', $package, $oldRoomType);
                        }
                    }
                } catch (\Exception $e) {
                    $info['error']++;
                    $this->logPackage('ERROR: ' . $e->getMessage(), $package, $oldRoomType);
                }
                $this->dm->clear($package);
            }
            $this->dm->flush();
            $this->dm->clear();
        }

        $this->logger->alert('Final TOTAL: ' . $info['amount'] . "\n");
        $this->logger->alert('Final ERROR: ' . $info['error'] . "\n");
        $this->logger->alert('---------END---------');

        return $info;
    }

    /**
     * @param PackageMovingInfo $packageMovingInfo
     * @return PackageMovingInfo
     */
    public function fillMovingPackageData(PackageMovingInfo $packageMovingInfo): PackageMovingInfo
    {
        $queryBuilder = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('begin')->lte($packageMovingInfo->getEnd())
            ->field('end')->gte($packageMovingInfo->getBegin());

        if ($packageMovingInfo->getRoomTypes()->count() > 0) {
            $handledRoomTypes = $packageMovingInfo->getRoomTypes();
        } else {
            $handledRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->createQueryBuilder()
                ->field('isEnabled')->equals(true)
                ->getQuery()
                ->execute()
                ->toArray();
        }

        $roomTypeIds = [];
        foreach ($handledRoomTypes as $handledRoomType) {
            if ($this->hasLessAvailability($handledRoomType)) {
                $roomTypeIds[] = $handledRoomType->getId();
            }
        }

        $queryBuilder->field('roomType.id')->in($roomTypeIds);

        $handledPackagesCount = $queryBuilder->getQuery()->count();
        $packagesPerIteration = 50;

        for ($i = 0; $i <= ceil($handledPackagesCount / $packagesPerIteration); $i++) {
            $packages = $queryBuilder
                ->skip($packagesPerIteration * $i)
                ->limit($packagesPerIteration)
                ->getQuery()
                ->execute();

            /** @var Package $package */
            foreach ($packages as $package) {
                if ($package->getCountPersons() < $package->getRoomType()->getTotalPlaces() && $package->getIsMovable()) {

                    $optimalRoomType = $this->getOptimalRoomType($package);
                    if (!is_null($optimalRoomType) && $optimalRoomType->getId() != $package->getRoomType()->getId()) {
                        $movingPackageData = (new MovingPackageData())
                            ->setNewRoomType($optimalRoomType)
                            ->setOldRoomType($package->getRoomType())
                            ->setOldAccommodation($package->getAccommodation())
                            ->setPackage($package);
                        $packageMovingInfo->addMovingPackageData($movingPackageData);
                    }
                }
            }

            $this->dm->flush();
        }

        $packageMovingInfo->setStatus(PackageMovingInfo::READY_STATUS);
        $this->dm->flush();

        return $packageMovingInfo;
    }

    /**
     * Проверяет, является ли тип комнаты самым маленьким в категории
     * @param RoomType $roomType
     * @return bool
     */
    private function hasLessAvailability(RoomType $roomType)
    {
        /** @var RoomTypeCategory $roomTypeCategory */
        $roomTypeCategory = $roomType->getCategory();
        if ($roomTypeCategory->getTypes()->count() == 1) {
            return false;
        }

        foreach ($roomTypeCategory->getTypes() as $iteratedRoomType) {
            if ($iteratedRoomType->getTotalPlaces() < $roomType->getTotalPlaces()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PackageMovingInfo $packageMovingInfo
     * @param $text
     * @param $subject
     * @param $template
     */
    public function sendPackageMovingMail(PackageMovingInfo $packageMovingInfo, $text, $subject, $template)
    {
        $mailer = $this->container->get('mbh.notifier.mailer');

        $message = $mailer::createMessage();
        $message
            ->setText($text)
            ->setFrom('system')
            ->setSubject($subject)
            ->setType('info')
            ->setCategory('notification')
            ->setTemplate($template)
            ->setAdditionalData([
                'movingInfo' => $packageMovingInfo,
            ])
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $mailer
            ->setMessage($message)
            ->notify();
    }

    /**
     * @param Package $package
     * @return \MBH\Bundle\HotelBundle\Document\RoomType|null
     */
    public function getOptimalRoomType(Package $package)
    {
        //query Search
        $query = new SearchQuery();
        $countRoom = $package->getRoomType()->getTotalPlaces();

        $roomTypesByCategory = $package->getRoomType()->getCategory()->getTypes();
        foreach ($roomTypesByCategory as $roomTypeCategory) {
            if ($roomTypeCategory->getTotalPlaces() < $countRoom) {
                $query->roomTypes[] = $roomTypeCategory->getId();
            }
        }
        if (count($query->roomTypes) == 0) {
            return null;
        }

        $query->begin = clone $package->getBegin();
        $query->end = clone $package->getEnd();
        $query->adults = $package->getAdults();
        $query->children = $package->getChildren();
        $query->forceRoomTypes = true;

        $groupedResult = $this->packageSearch->search($query);
        if (!$groupedResult) {
            return null;
        }

        usort($groupedResult, function ($a, $b) {
            /** @var SearchResult $a */
            /** @var SearchResult $b */
            if ($a->getRoomType()->getTotalPlaces() == $b->getRoomType()->getTotalPlaces()) {
                return 0;
            }

            return ($a->getRoomType()->getTotalPlaces() < $b->getRoomType()->getTotalPlaces()) ? -1 : 1;
        }
        );

        return $groupedResult[0]->getRoomType();
    }

    /**
     * @param $roomTypesByCategories
     * @return Package
     */
    protected function getMaxAmountPackages($roomTypesByCategories)
    {
        return $this->dm->getRepository('MBHPackageBundle:Package')->getPackageCategory(
            new \DateTime(self::BEGIN),
            new \DateTime(self::END),
            $roomTypesByCategories ? array_keys($roomTypesByCategories->toArray()) : null,
            true
        );
    }

    /**
     * @param null $roomTypesByCategories
     * @param $skip
     * @param $count
     * @return Package
     */
    public function getPackages($roomTypesByCategories = null, $skip, $count)
    {
        return $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getPackageCategory(
                new \DateTime(self::BEGIN),
                new \DateTime(self::END),
                $roomTypesByCategories ? array_keys($roomTypesByCategories->toArray()) : null,
                false,
                true,
                $skip * $count
            );
    }

    /**
     * @param $config
     * @return mixed
     */
    protected function roomTypeByCategories(RoomTypeZip $config)
    {
        return $this->dm->getRepository('MBHHotelBundle:RoomType')->roomByCategories(
            $config->getHotel(),
            $this->helper->toIds($config->getCategories())
        );
    }

    /**
     * @param $message
     * @param Package $package
     * @param RoomType $oldRoomType
     */
    protected function logPackage($message, Package $package, RoomType $oldRoomType)
    {
        $this->logger->info(
            $message,
            [
                'Begin' => $package->getBegin()->format('d-m-Y'),
                'End' => $package->getEnd()->format('d-m-Y'),
                'id' => $package->getId(),
                'OldRoomTypeId' => $oldRoomType->getId(),
                'oldRoomTypeName' => $oldRoomType->getName(),
                'NewRoomType_id' => $package->getRoomType()->getId(),
                'NewRoomTypeName' => $package->getRoomType()->getName(),
                'Tariff_id' => $package->getTariff()->getId(),
                'CreatedBy' => $package->getCreatedBy(),
                'NumberWithPrefix' => $package->getNumberWithPrefix(),
                'Price' => $package->getPrice(),
                'TotalPrice' => $package->getTotalOverwrite(),
            ]
        );
    }

}