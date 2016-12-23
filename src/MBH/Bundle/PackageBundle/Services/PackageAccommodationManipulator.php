<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

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
     * PackageAccommodationManipulator constructor.
     * @param $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
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
        /** @var PersistentCollection $pAccammodations */
        $pAccammodations = $package->getAccommodations();

        if (!$pAccammodations->count()) {
            $interval = [
                'packageAccommodationId' => null,
                'begin' => $package->getBegin(),
                'end' => $package->getEnd(),
            ];
            $intervals->add($interval);

        } else {

            $pAccammodationsSorted = $this->sortAccommodationsByBeginDate($pAccammodations);
            $iterator = $pAccammodationsSorted->getIterator();
            while ($iterator->current() !== null) {
                /** @var PackageAccommodation $accommodation */
                $accommodation = $iterator->current();
                $begin = $accommodation->getBegin();
                if (isset($end) && $begin != $end) {
                    $interval = [
                        'begin' => $end,
                        'end' => $begin
                    ];
                    $intervals->add($interval);
                }
                $end = $accommodation->getEnd();
                $iterator->next();
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
     * @param Collection $packageAccommodations
     * @return ArrayCollection
     */
    private function sortAccommodationsByBeginDate(Collection $packageAccommodations): ArrayCollection
    {
        $data = $packageAccommodations->toArray();
        usort($data, function ($a, $b) {
            /** @var PackageAccommodation $a*/
            /** @var PackageAccommodation $b*/
            $c = 'd';
            return ($a->getBegin() < $b->getBegin())? -1 : 1;
        });

        return new ArrayCollection($data);
    }



}