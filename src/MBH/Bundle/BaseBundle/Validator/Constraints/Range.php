<?php


namespace MBH\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Range
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @Annotation
 */
class Range extends Constraint
{
    public $message = 'The filed "%firstProperty%" should be more than "%secondProperty%".';
    public $firstProperty = 'begin';
    public $secondProperty = 'end';

    public function validatedBy()
    {
        return 'mbh_range';
    }
}