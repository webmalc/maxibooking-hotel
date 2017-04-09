<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PackageServiceValidator extends ConstraintValidator
{

    public function validate($packageService, Constraint $constraint)
    {
        $package = $packageService->getPackage();
        $service  = $packageService->getService();
        $calcType = $service->getCalcType();

        if ($package && $calcType == 'per_stay') {
            if ($package->getEnd() < $packageService->getEnd() ||
                $package->getBegin() > $packageService->getBegin()
            ) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
