<?php
/**
 * Created by PhpStorm.
 * Date: 04.06.18
 */

namespace MBH\Bundle\OnlineBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsPhoneOrEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $email = strpos($value, '@') !== false;
        $phone = (bool) preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $value, $match);
        if (!$email && !$phone) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}