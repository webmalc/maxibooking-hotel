<?php
/**
 * Created by PhpStorm.
 * Date: 04.06.18
 */

namespace MBH\Bundle\OnlineBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class ContainsPhoneOrEmail extends Constraint
{
    public $message = 'The string "%string%" no phone or email';
}