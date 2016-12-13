<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PackageAccommodations extends Constraint
{
    /**
     * @var
     */
    public $wrongStartDateMessage = 'validator.accommodation.wrong.start';

    /**
     * @var
     */
    public $wrongEndDateMessage = 'validator.accommodation.wrong.end';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}