<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

class PackageAccommodationManipulator
{

    private $dm;

    /**
     * PackageAccommodationManipulator constructor.
     * @param $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function splitAccommodation(PackageAccommodation $accommodation, \DateTime $splitDate)
    {

    }

    public function unionAccommodations(PackageAccommodation $accommodation)
    {

    }

    public function unionAccommodationsInPackage(PackageAccommodation $packageAccommodation)
    {

    }

    public function editAccommodation(PackageAccommodation $accommodation, \DateTime $startDate, \DateTime $endDate)
    {

    }


}