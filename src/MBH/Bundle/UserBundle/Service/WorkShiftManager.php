<?php

namespace MBH\Bundle\UserBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Loggable\Document\LogEntry;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\UserBundle\Document\WorkShift;

/**
 * Class WorkShiftManager

 */
class WorkShiftManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var HotelSelector
     */
    protected $hotelSelector;

    public function __construct(DocumentManager $dm, HotelSelector $hotelSelector)
    {
        $this->dm = $dm;
        $this->hotelSelector = $hotelSelector;
    }

    /**
     * @return WorkShift
     */
    public function create()
    {
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $hotel = $this->hotelSelector->getSelected();

        $roomTypeIDs = [];
        foreach($hotel->getRoomTypes() as $roomType) {
            $roomTypeIDs[] = $roomType->getId();
        };
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy([
            'isCheckIn' => true, 'isCheckOut' => false, 'arrivalTime' => ['$lte' => new \DateTime()],
            'roomType.id' => ['$in' => $roomTypeIDs]
        ]);//todo aggregation
        $beginGuestTotal = 0;
        $beginTouristTotal = 0;
        foreach($packages as $package) {
            $beginGuestTotal += $package->getAdults() + $package->getChildren();
            $beginTouristTotal += count($package->getTourists());
        }

        $workShift = new WorkShift();
        $workShift
            ->setBegin(new \DateTime())
            ->setStatus(WorkShift::STATUS_OPEN)
            ->setBeginGuestTotal($beginGuestTotal)
            ->setBeginTouristTotal($beginTouristTotal)
        ;

        $this->dm->persist($workShift);
        $this->dm->flush();

        return $workShift;
    }

    public function lock(WorkShift $workShift)
    {
        $this->fillLockWorkShift($workShift);

        $this->dm->persist($workShift);
        $this->dm->flush();

        return true;
    }

    protected function fillLockWorkShift(WorkShift $workShift)
    {
        $workShift->setEnd(new \DateTime());

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $hotel = $workShift->getHotel();
        $roomTypeIDs = [];
        foreach($hotel->getRoomTypes() as $roomType) {
            $roomTypeIDs[] = $roomType->getId();
        }

        $criteria = ['roomType.id' => ['$in' => $roomTypeIDs]];

        /** @var Package[] $packages */
        $packages = $packageRepository->findBy($criteria + ['isCheckIn' => false, 'begin' => ['$gte' => new \DateTime()]]);//todo aggregation
        $endGuestTotal = 0;
        foreach($packages as $package) {
            $endGuestTotal += $package->getAdults() + $package->getChildren();
        }

        $arrivalTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy($criteria + ['arrivalTime' => ['$gte' => $workShift->getBegin(), '$lte' => $workShift->getEnd()]]);//todo aggregation
        foreach($packages as $package) {
            $arrivalTouristTotal += count($package->getTourists());
        }
        $noArrivalTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy($criteria + ['begin' => ['$gte' => $workShift->getBegin(), '$lte' => $workShift->getEnd()], 'isCheckIn' => false]);//todo aggregation
        foreach($packages as $package) {
            $noArrivalTouristTotal += count($package->getTourists());
        }

        /*$this->dm->getRepository('Gedmo\Loggable\Document\LogEntry')
            ->createQueryBuilder()
            ->field('objectClass')->equals(Package::class)
            ->field('loggedAt')->gte($workShift->getBegin())->lte(new \DateTime())
        ;*/
        $begin = $workShift->getBegin();
        $end = $workShift->getEnd();
        $packages = $packageRepository->findBy($criteria + [
            '$or' => [
                [
                    'begin' => ['$lte' => $begin],
                    'end' => ['$gte' => $begin]
                ],
                [
                    'begin' => ['$lte' => $end],
                    'end' => ['$gte' => $end]
                ],
                [
                    'begin' => ['$gte' => $begin],
                    'end' => ['$lte' => $end]
                ]
            ],
        ]);

        $continuePackageTotal = 0;
        $logEntryRepository = $this->dm->getRepository(LogEntry::class);
        foreach($packages as $package) {
            /** @var LogEntry[] $logEntries */
            $logEntries = $logEntryRepository->createQueryBuilder()
                ->field('objectClass')->equals(Package::class)
                ->field('objectId')->equals($package->getId())
                ->getQuery()->execute()
            ;
            foreach($logEntries as $logEntry) {
                $data = $logEntry->getData();
                if(isset($data['end']) && $data['end'] < $package->getEnd()) {
                    $continuePackageTotal++;
                }
            }
        }

        $departureTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy($criteria + ['departureTime' => ['$gte' => $workShift->getBegin(), '$lte' => $workShift->getEnd()]]);//todo aggregation
        foreach($packages as $package) {
            $departureTouristTotal += count($package->getTourists());
        }
        $noDepartureTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy($criteria + ['end' => ['$gte' => $workShift->getBegin(), '$lte' => $workShift->getEnd()], 'isCheckOut' => false]);//todo aggregation
        foreach($packages as $package) {
            $noDepartureTouristTotal += count($package->getTourists());
        }

        $cashIncomeTotal = 0;
        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
            ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
            ->field('createdBy')->equals($workShift->getCreatedBy())
            ->field('method')->equals('cash')
            ->field('operation')->equals('in')
            ->getQuery()->execute()
        ;
        foreach($cashDocuments as $cashDocument) {
            $cashIncomeTotal += $cashDocument->getTotal();
        }

        $electronicCashIncomeTotal = 0;
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
            ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
            ->field('createdBy')->equals($workShift->getCreatedBy())
            ->field('method')->equals('electronic')
            ->field('operation')->equals('in')
            ->getQuery()->execute()
        ;
        foreach($cashDocuments as $cashDocument) {
            $electronicCashIncomeTotal += $cashDocument->getTotal();
        }

        $cashExpenseTotal = 0;
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
            ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
            ->field('createdBy')->equals($workShift->getCreatedBy())
            ->field('operation')->equals('out')
            ->getQuery()->execute()
        ;
        foreach($cashDocuments as $cashDocument) {
            $cashExpenseTotal += $cashDocument->getTotal();
        }

        $workShift
            ->setStatus(WorkShift::STATUS_LOCKED)
            ->setEndGuestTotal($endGuestTotal)
            ->setArrivalTouristTotal($arrivalTouristTotal)
            ->setNoArrivalTouristTotal($noArrivalTouristTotal)
            ->setContinuePackageTotal($continuePackageTotal)
            ->setDepartureTouristTotal($departureTouristTotal)
            ->setNoDepartureTouristTotal($noDepartureTouristTotal)
            ->setCashIncomeTotal($cashIncomeTotal)
            ->setElectronicCashIncomeTotal($electronicCashIncomeTotal)
            ->setCashExpenseTotal($cashExpenseTotal)
        ;
    }
}