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
class PhoneOrEmail extends Constraint
{
    public $message = 'The string "%string%" not phone or email';
}