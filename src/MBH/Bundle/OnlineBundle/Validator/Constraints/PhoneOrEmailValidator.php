<?php
/**
 * Created by PhpStorm.
 * Date: 04.06.18
 */

namespace MBH\Bundle\OnlineBundle\Validator\Constraints;


use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneOrEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $isValid = false;

        $email = preg_match('/^.+\@\S+\.\S+$/', $value);

        if ($email) {
            $isValid = true;
        }

        if (!$isValid) {
            if (!empty(Tourist::cleanPhone($value))) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}