<?php
namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PackageService extends Constraint
{
    public $error = 'package.service.validator.error';

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {

        return 'mbh.package_service.validator';
    }
}