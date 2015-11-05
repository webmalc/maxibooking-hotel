<?php

namespace MBH\Bundle\UserBundle\Controller;

use Gedmo\Loggable\Document\LogEntry;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\UserBundle\Document\WorkShift;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/work-shift")
 */
class WorkShiftController extends Controller
{
    /**
     * @Route("/", name="work_shift")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return [

        ];
    }

    /**
     * @Route("/wait", name="work_shift_wait")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function waitAction()
    {
        $workShift = $this->dm->getRepository('MBHUserBundle:WorkShift')->findCurrent($this->getUser());

        if($workShift && $workShift->getStatus() == WorkShift::STATUS_LOCKED) {
            return [
                'workShift' => $workShift
            ];
        }

        throw $this->createNotFoundException();
    }

    /**
     * @Route("/start/{id}", name="work_shift_start")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @ParamConverter(class="MBH\Bundle\UserBundle\Document\WorkShift")
     */
    public function startAction(WorkShift $workShift)
    {
        if($workShift->getStatus() != WorkShift::STATUS_LOCKED) {
            throw $this->createNotFoundException();
        }
        $workShift->setStatus(WorkShift::STATUS_OPEN);
        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/new", name="work_shift_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function newAction()
    {
        $workShift = new WorkShift();

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy(['checkIn' => false, 'begin' => ['$gte' => new \DateTime()]]);//todo aggregation
        $beginGuestTotal = 0;
        $beginTouristTotal = 0;
        foreach($packages as $package) {
            $beginGuestTotal += $package->getAdults() + $package->getChildren();
            $beginTouristTotal += count($package->getTourists());
        }

        $workShift
            ->setBegin(new \DateTime())
            ->setStatus(WorkShift::STATUS_OPEN)
            ->setBeginGuestTotal($beginGuestTotal)
            ->setBeginTouristTotal($beginTouristTotal)
        ;


        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/lock", name="work_shift_lock")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function lockAction()
    {
        $workShiftRepository = $this->dm->getRepository('MBHUserBundle:WorkShift');
        $workShift = $workShiftRepository->findCurrent($this->getUser());

        $workShift
            ->setEnd(new \DateTime())
        ;

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        //todo move to special service object
        $arrivalTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy(['arrivalTime' => ['$gte' => $workShift->getBegin(), '$lte' => new \DateTime()]]);//todo aggregation
        foreach($packages as $package) {
            $arrivalTouristTotal += count($package->getTourists());
        }
        $noArrivalTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy(['begin' => ['$gte' => $workShift->getBegin(), '$lte' => new \DateTime()], 'checkIn' => false]);//todo aggregation
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
        $packages = $packageRepository->findBy([
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
        foreach($packages as $package) {
            /** @var LogEntry[] $logEntries */
            $logEntries = $this->dm->getRepository(LogEntry::class)->createQueryBuilder()
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
        $packages = $packageRepository->findBy(['departureTime' => ['$gte' => $workShift->getBegin(), '$lte' => new \DateTime()]]);//todo aggregation
        foreach($packages as $package) {
            $departureTouristTotal += count($package->getTourists());
        }
        $noDepartureTouristTotal = 0;
        /** @var Package[] $packages */
        $packages = $packageRepository->findBy(['end' => ['$gte' => $workShift->getBegin(), '$lte' => new \DateTime()], 'checkOut' => false]);//todo aggregation
        foreach($packages as $package) {
            $noDepartureTouristTotal += count($package->getTourists());
        }

        $cashIncomeTotal = 0;
        $cashDocumentRepository = $this->dm->getRepository('MBHCashBundle:CashDocument');
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
            ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
            ->field('createdBy.id')->equals($workShift->getId())
            ->field('method')->equals('cash')
            ->field('operation')->equals('in')
        ;
        foreach($cashDocuments as $cashDocument) {
            $cashIncomeTotal += $cashDocument->getTotal();
        }

        $electronicCashIncomeTotal = 0;
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
            ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
                ->field('createdBy.id')->equals($workShift->getId())
                ->field('method')->equals('electronic')
                ->field('operation')->equals('in')
            ;
        foreach($cashDocuments as $cashDocument) {
            $electronicCashIncomeTotal += $cashDocument->getTotal();
        }

        $cashExpenseTotal = 0;
        $cashDocuments = $cashDocumentRepository->createQueryBuilder() // todo aggregation
        ->field('createdAt')->gte($workShift->getBegin())->lte($workShift->getEnd())
            ->field('createdBy.id')->equals($workShift->getId())
            ->field('operation')->equals('out')
        ;
        foreach($cashDocuments as $cashDocument) {
            $cashExpenseTotal += $cashDocument->getTotal();
        }

        $workShift
            ->setStatus(WorkShift::STATUS_LOCKED)
            ->setArrivalTouristTotal($arrivalTouristTotal)
            ->setNoArrivalTouristTotal($noArrivalTouristTotal)
            ->setContinuePackageTotal($continuePackageTotal)
            ->setDepartureTouristTotal($departureTouristTotal)
            ->setNoDepartureTouristTotal($noDepartureTouristTotal)
            ->setCashIncomeTotal($cashIncomeTotal)
            ->setElectronicCashIncomeTotal($electronicCashIncomeTotal)
            ->setCashExpenseTotal($cashExpenseTotal)
        ;

        $this->dm->persist($workShift);
        $this->dm->flush();

        return $this->redirectToRoute('work_shift_wait');
    }
}