<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PackageAccommodationsValidator extends ConstraintValidator
{
    private $dm;

    /**
     * PackageAccommodationValidator constructor.
     * @param $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function validate($packageAccommodation, Constraint $constraint)
    {
        /** @var PackageAccommodation $packageAccommodation */
        $package = !is_null($packageAccommodation->getPackageForValidator())
            ? $packageAccommodation->getPackageForValidator()
            : $this->dm->getRepository('MBHPackageBundle:Package')
            ->getPackageByPackageAccommodationId($packageAccommodation->getId());

        if (is_null($package) || !empty($package->getDeletedAt())) {
            $this->context->buildViolation($constraint->packageIsCancelled)->addViolation();
        }
        //Check PackageAccommodation by Package
        if ($packageAccommodation->getBegin() < $package->getBegin() || $packageAccommodation->getEnd() > $package->getEnd()) {
            $this->context->buildViolation($constraint->wrongStartOrEndMessage)->addViolation();
        }
        //Check intersect PakageAccommodation by neighbors
        /** @var PackageAccommodation $packageAccommodation */
        $accommodations = $package->getAccommodations();
        foreach ($accommodations as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            if ($accommodation->getId() != $packageAccommodation->getId()) {
                if ($packageAccommodation->getEnd() > $accommodation->getBegin() && $packageAccommodation->getBegin() < $accommodation->getEnd()) {
                    $this->context->buildViolation($constraint->intersectNeighbour)->addViolation();
                }
            }
        }
        if ($packageAccommodation->getEnd() <= $packageAccommodation->getBegin()) {
            $this->context->buildViolation($constraint->endLessOrEqualBegin)->addViolation();
        }
    }
}