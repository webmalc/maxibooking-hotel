<?php

namespace MBH\Bundle\BaseBundle\Lib\Disableable;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 * Class Disableable
 * @package MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable
 */
final class Disableable extends Annotation
{
    /** @var string */
    public $fieldName = 'isEnabled';
}