<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PackageAccommodationManipulator
 * @package MBH\Bundle\PackageBundle\Services
 */
class PackageAccommodationManipulator
{

    /**
     * @var DocumentManager
     */
    private $dm;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * PackageAccommodationManipulator constructor.
     * @param DocumentManager $dm
     * @param TranslatorInterface $translator
     */
    public function __construct(DocumentManager $dm, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->translator = $translator;
    }

    public function addAccommodation(PackageAccommodation $accommodation, Package $package)
    {
        if ($accommodation->getBegin() < $package->getBegin() || $accommodation->getEnd() > $package->getEnd()) {
            return $this->translator->trans('accommodation_manipulator.error.incorrect_acc_to_package_dates');
        }
        if ($accommodation->getBegin() <= $accommodation->getEnd()) {
            return $this->translator->trans('controller.packageController.accommodation_add.begin_equal_or_later_end_error');
        }
        $existedAccommodations = $this->dm->getRepository('MBHPackageBundle:PackageAccommodation')
            ->fetchWithAccommodation($accommodation->getBegin(),
                $accommodation->getEnd(),
                $accommodation->getAccommodation()->getId());
        if (count($existedAccommodations) > 0) {
            return $this->translator->trans('accommodation_manipulator.error.room_busy',
                ['%roomName%' => $accommodation->getAccommodation()->getName()]);
        }

        $package->addAccommodation($accommodation);
        $this->dm->flush();

        return $accommodation;
    }

    /**
     * @param PackageAccommodation $accommodation
     * @param \DateTime $splitDate
     */
    public function splitAccommodation(PackageAccommodation $accommodation, \DateTime $splitDate)
    {

    }

    /**
     * @param PackageAccommodation $accommodation
     */
    public function unionAccommodations(PackageAccommodation $accommodation)
    {

    }

    /**
     * @param PackageAccommodation $packageAccommodation
     */
    public function unionAccommodationsInPackage(PackageAccommodation $packageAccommodation)
    {

    }

    /**
     * @param PackageAccommodation $accommodation
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function editAccommodation(PackageAccommodation $accommodation, \DateTime $startDate, \DateTime $endDate)
    {

    }

    /**
     * @param Package $package
     * @return ArrayCollection
     */
    public function getEmptyIntervals(Package $package): ArrayCollection
    {
        $intervals = new ArrayCollection();
        /** @var PersistentCollection $pAccommodations */
        $pAccommodations = $package->getAccommodations();

        if (!$pAccommodations->count()) {
            $interval = [
                'packageAccommodationId' => null,
                'begin' => $package->getBegin(),
                'end' => $package->getEnd(),
            ];
            $intervals->add($interval);
        } else {
            /** @var PackageAccommodation $previousAccommodation */
            $previousAccommodation = null;
            foreach ($pAccommodations as $accommodation) {
                /** @var PackageAccommodation $accommodation */
                if (!$previousAccommodation) {
                    if ($package->getBegin()->getTimestamp() != $accommodation->getBegin()->getTimestamp()) {
                        $intervals->add([
                            'begin' => $package->getBegin(),
                            'end' => $accommodation->getBegin()
                        ]);
                    }
                } else {
                    if ($previousAccommodation->getEnd()->getTimestamp() != $accommodation->getBegin()->getTimestamp()) {
                        $intervals->add([
                            'begin' => $previousAccommodation->getEnd(),
                            'end' => $accommodation->getBegin()
                        ]);
                    }
                }
                $previousAccommodation = $accommodation;
            }

            $lastIntervalEnd = $intervals->last() ? $intervals->last()['end'] : null;
            $lastAccommodationEnd = $pAccommodations->last()->getEnd();
            $packageEnd = $package->getEnd();

            if ($lastIntervalEnd) {
                $lastPeriodEnd = $lastIntervalEnd > $lastAccommodationEnd ? $lastIntervalEnd : $lastAccommodationEnd;
            } else {
                $lastPeriodEnd = $lastAccommodationEnd;
            }

            if ($lastPeriodEnd < $packageEnd) {
                $intervals->add([
                    'begin' => $lastPeriodEnd,
                    'end' => $package->getEnd()
                ]);
            }
        }

        return $intervals;
    }

    /**
     * @param Package $package
     * @return bool
     */
    public function isFullAccommodation(Package $package): bool
    {
        return ! (bool)$this->getEmptyIntervals($package)->count();
    }

    /**
     * @param $packageAccommodations
     * @return ArrayCollection
     */
    public function sortAccommodationsByBeginDate($packageAccommodations) : ArrayCollection
    {
        usort($packageAccommodations, function ($a, $b) {
            /** @var PackageAccommodation $a*/
            /** @var PackageAccommodation $b*/
            $c = 'd';
            return ($a->getBegin() < $b->getBegin())? -1 : 1;
        });

        return new ArrayCollection($packageAccommodations);
    }



}