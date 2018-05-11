<?php


namespace MBH\Bundle\SearchBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ChildrenAgesSameAsChildren extends Constraint
{
    public $message = 'The children ages num not same as children num';

    /**
     * @return string
     */
    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}