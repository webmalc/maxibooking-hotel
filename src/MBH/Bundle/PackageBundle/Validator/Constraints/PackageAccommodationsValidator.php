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
        $package = $packageAccommodation->getPackage();

        if ($packageAccommodation->getBegin() < $package->getCurrentAccommodationBegin()) {
            $this->context->buildViolation($constraint->wrongStartDateMessage)
                ->addViolation()
            ;
        }

        if ($packageAccommodation->getEnd() > $package->getEnd()) {
            $this->context->buildViolation($constraint->wrongEndDateMessage)
                ->addViolation()
            ;
        }

    }

}