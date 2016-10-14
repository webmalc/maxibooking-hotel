<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PackageServiceValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \MBH\Bundle\PackageBundle\Document\PackageService $packageService
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($packageService, Constraint $constraint)
    {

        if ($packageService->getPackage()->isEarlyCheckIn() && $packageService->getService()->getCode() == 'Early check-in') {
            $this->context->buildViolation($constraint->error)->addViolation();
        }
        if ($packageService->getPackage()->isLateCheckOut() && $packageService->getService()->getCode() == 'Late check-out') {
            $this->context->addViolation($constraint->error);
        }

        return true;
    }

}
