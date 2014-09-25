<?php

namespace MBH\Bundle\HotelBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HotelValidator extends ConstraintValidator
{

    public function validate($object, Constraint $constraint)
    {
        if((!empty($object->getMinPackageDuration()) && !empty($object->getMaxPackageDuration())) && $object->getMinPackageDuration() > $object->getMaxPackageDuration()) {
            $this->context->addViolation($constraint->packageDurationValidator);
        }

        return true;
    }

}
