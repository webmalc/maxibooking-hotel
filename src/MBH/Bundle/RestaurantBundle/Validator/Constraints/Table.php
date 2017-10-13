<?php
namespace MBH\Bundle\RestaurantBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Table extends Constraint
{

    public $messageError = 'validator.document.table.table_can_have_less_chairs';

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