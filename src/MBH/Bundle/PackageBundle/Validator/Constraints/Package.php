<?php
namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Package extends Constraint
{
    public $beginEndMessage = 'validator.begin_end_message';

    public $placesMessage = 'validator.places_message';
    
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