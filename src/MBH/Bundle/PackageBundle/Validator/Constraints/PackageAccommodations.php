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

    public $endLessOrEqualBegin = 'validator.accommodation.end_less_or_equal_begin';

    public $packageIsCancelled = 'validator.accommodation.package_is_cancelled';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
