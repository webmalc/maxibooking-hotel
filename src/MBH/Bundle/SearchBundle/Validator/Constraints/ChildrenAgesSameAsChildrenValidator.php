<?php


namespace MBH\Bundle\SearchBundle\Validator\Constraints;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChildrenAgesSameAsChildrenValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint): void
    {
        /** @var SearchConditions $object */
        $children = (int)$object->getChildren();
        $ages = \count($object->getChildrenAges());
        if ($children !== $ages) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

}