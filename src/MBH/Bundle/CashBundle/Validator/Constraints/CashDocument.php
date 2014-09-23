<?php
namespace MBH\Bundle\CashBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CashDocument extends Constraint
{
    public $message = 'Сумма расхода по брони не должна превышать приход. Сумма прихода: %total%';
    
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