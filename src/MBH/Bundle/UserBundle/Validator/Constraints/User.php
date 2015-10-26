<?php
namespace MBH\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class User extends Constraint
{
    public $message = 'validator.user.default_notice_count';

    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'mbh.user.validator';
    }
}