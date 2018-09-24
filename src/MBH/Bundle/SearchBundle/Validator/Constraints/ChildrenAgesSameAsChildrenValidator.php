<?php


namespace MBH\Bundle\SearchBundle\Validator\Constraints;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChildrenAgesSameAsChildrenValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint): void
    {
        $isThisWarmUp = false;
        /** TODO: fixIT! */
        if ($object instanceof SearchConditions) {
            $isThisWarmUp = $object->isThisWarmUp();
        }
        /** @var SearchConditions $object */
        if (!$isThisWarmUp) {
            $children = (int)$object->getChildren();
            $ages = \count($object->getChildrenAges());
            if ($children !== $ages) {
                /** @var ChildrenAgesSameAsChildren $constraint */
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }

            $adults = (int)$object->getAdults();
            if ($adults === 0 && $children > 0) {
                $this->context
                    ->buildViolation($constraint->wrongAdultsCountMessage)
                    ->addViolation()
                ;
            }
        }

    }

}