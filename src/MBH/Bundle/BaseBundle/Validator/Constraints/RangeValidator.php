<?php

namespace MBH\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class RangeValidator

 */
class RangeValidator extends ConstraintValidator
{
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof Range) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Range');
        }

        $constraint->firstProperty;
        $constraint->secondProperty;
        $firstValue = $this->extractValue($entity, $constraint->firstProperty);
        $secondValue = $this->extractValue($entity, $constraint->secondProperty);

        if (!$firstValue instanceof \DateTimeInterface || !$secondValue instanceof \DateTimeInterface) {
            return;
        }

        if ($firstValue->getTimestamp() > $secondValue->getTimeStamp()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%firstProperty%', $constraint->firstProperty)
                ->setParameter('%secondProperty%', $constraint->secondProperty)
                ->addViolation();

            return false;
        }

        return true;
    }

    /**
     * From getter method
     *
     * @param $entity
     * @param $propertyName
     * @return mixed
     */
    private function extractValue($entity, $propertyName)
    {
        return call_user_func([$entity, 'get'.ucfirst($propertyName)]);
    }
}