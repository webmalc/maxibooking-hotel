<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PackageAccommodations extends Constraint
{

    public $wrongStartOrEndMessage = 'validator.accommodation.wrong.start.end';

    public $intersectNeighbour = 'validator.accommodation.intersect.neighbour';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}