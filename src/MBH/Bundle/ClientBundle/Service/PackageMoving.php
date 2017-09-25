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
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\OrderManager;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PackageMoving
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
     * @return PackageMovingInfo
     */
    public function packagesZip()
    {
        /** @var RoomTypeZip $roomTypeZipConfig */
        $roomTypeZipConfig = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();

        /** @var PackageMovingInfo $existingReport */
        $existingReport = $this->dm
            ->getRepository('MBHPackageBundle:PackageMovingInfo')
            ->createQueryBuilder()
            ->limit(1)
            ->field('status')->notEqual(PackageMovingInfo::OLD_REPORT_STATUS)
            ->getQuery()
            ->getSingleResult();

        if (!is_null($existingReport)) {
            if ($existingReport->getStatus() === $existingReport::PREPARING_STATUS) {
                return $existingReport;
            }
            if ($existingReport->getStatus() === $existingReport::READY_STATUS) {
                $existingReport->setStatus($existingReport::OLD_REPORT_STATUS);
                $this->dm->flush();
            }
        }

        $begin = new \DateTime('midnight');
        $end = new \DateTime('1 October');

        $oldReportDatesDifference = date_diff($begin, $end);
        $newReport = (new PackageMovingInfo())
            ->setStartAt(new \DateTime())
            ->setBegin(new \DateTime())
            ->setEnd((new \DateTime())->add($oldReportDatesDifference));

        foreach ($roomTypeZipConfig->getCategories() as $roomTypeCategory) {
            foreach ($roomTypeCategory->getTypes() as $roomType) {
                $newReport->addRoomType($roomType);
            }
        }

        $this->dm->persist($newReport);
        $this->dm->flush();

        $this->container->get('old_sound_rabbit_mq.task_prepare_package_moving_report_producer')
            ->publish(
                serialize(
                    [
                        'packageMovingInfoId' => $newReport->getId()
                    ]
                )
            );

        return $newReport;
    }

    /**
     * @param PackageMovingInfo $movingInfo
     */
    public function updatePackageMovingInfo(PackageMovingInfo $movingInfo)
    {
        foreach ($movingInfo->getMovingPackagesData() as $packageData) {
            if (!$packageData->getPackage()->getIsMovable()) {
                $movingInfo->removeMovingPackageData($packageData);
            } else {
                if (!$packageData->getIsMoved()) {
                    $optimalRoomType = $this->getOptimalRoomType($packageData->getPackage());
                    if (is_null($optimalRoomType)) {
                        $movingInfo->removeMovingPackageData($packageData);
                    } else {
                        $packageData->setNewRoomType($optimalRoomType);
                    }
                }
            }
        }
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
                if ($package->getCountPersons() < $package->getRoomType()->getTotalPlaces()
                    && $package->getIsMovable()
                    && empty($package->getAccommodation())
                ) {
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
            ->setCategory('report')
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
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param null $users
     * @param null $hotels
     * @return MovingPackageData[]
     */
    public function getMovedPackagesData(\DateTime $begin, \DateTime $end, $users = null, $hotels = null)
    {
        //Увеличиваю на день т.к. данные о времени закрытия отчета и перемещения записаны как дата и время, а значения окончания искомого периода времени в виде даты
        $filterEnd = (clone $end)->add(new \DateInterval('P1D'));
        $packageMovingInfoQB = $this->dm->getRepository('MBHPackageBundle:PackageMovingInfo')->createQueryBuilder();

        $packageMovingInfoQB
            //Отсеиваю старые записи
            ->field('startAt')->type('date')
            ->field('startAt')->gte((clone $begin)->modify('- 1 month'))
            ->field('status')->notEqual(PackageMovingInfo::PREPARING_STATUS);
        $packageMovingInfoQB->addAnd($packageMovingInfoQB->expr()
            ->addOr($packageMovingInfoQB->expr()->field('closedAt')->equals(null))
            ->addOr($packageMovingInfoQB->expr()->field('closedAt')->lte($filterEnd)));
        $packageMovingInfoQB->addAnd($packageMovingInfoQB->expr()
            ->addOr($packageMovingInfoQB->expr()->field('movingPackagesData.0')->notEqual(null))
            ->addOr($packageMovingInfoQB->expr()->field('movingPackagesData.0')->exists(true)));

        if (!is_null($users)) {
            $packageMovingInfoQB->field('runningBy.id')->in($this->helper->toIds($users));
        }

        $movedPackagesData = [];
        $chosenPackageMovingInfos = $packageMovingInfoQB->getQuery()->execute()->toArray();
        /** @var PackageMovingInfo $packageMovingInfo */
        foreach ($chosenPackageMovingInfos as $packageMovingInfo) {
            foreach ($packageMovingInfo->getMovingPackagesData() as $movingPackageData) {
                if ($movingPackageData->getIsMoved()
                    && $movingPackageData->getDateOfMove() < $filterEnd
                    && $movingPackageData->getDateOfMove() > $begin
                    && (is_null($hotels) || in_array($movingPackageData->getOldRoomType()->getHotel(), $hotels))
                ) {
                    $movedPackagesData[] = $movingPackageData;
                }
            }
        }

        return $movedPackagesData;
    }

    /**
     * @param Package $package
     * @return \MBH\Bundle\HotelBundle\Document\RoomType|null
     */
    public function getOptimalRoomType(Package $package)
    {
        $query = new SearchQuery();
        $countRoom = $package->getRoomType()->getTotalPlaces();

        $roomTypesByCategory = $package->getRoomType()->getCategory()->getTypes();
        foreach ($roomTypesByCategory as $roomType) {
            if ($roomType->getTotalPlaces() < $countRoom) {
                $query->roomTypes[] = $roomType->getId();
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
}