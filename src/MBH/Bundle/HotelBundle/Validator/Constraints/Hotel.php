<?php
namespace MBH\Bundle\HotelBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Hotel extends Constraint
{
    public $packageDurationValidator = 'Максимальная длина брони не должна быть меньше минимальной длины брони';

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return get_class($this).'Validator';;
    }
}