<?php
namespace MBH\Bundle\RestaurantBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Table extends Constraint
{

    public $messageError = 'Всего стол может иметь 20 стульев';

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {

        return 'mbh.table.validator';
    }
}