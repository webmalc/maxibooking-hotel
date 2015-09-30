<?php
namespace MBH\Bundle\PriceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Tariff extends Constraint
{
    public $messageDate = 'invalid.daterange';
    public $messageAges = 'invalid.ages';
    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}