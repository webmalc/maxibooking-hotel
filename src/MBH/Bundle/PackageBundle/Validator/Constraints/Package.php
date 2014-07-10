<?php
namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Package extends Constraint
{
    public $beginEndMessage = 'Начало путевки не должно быть больше или равно концу путевки';
    
    public $tariffMessage = 'Даты путевки выходят за пределы тарифа';
    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'mbh.package.validator';
    }
}